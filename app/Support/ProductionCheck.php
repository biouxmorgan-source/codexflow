<?php

namespace App\Support;

use App\Notifications\Channels\PushChannel;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Vérification d'une installation avant et après la mise en ligne (php artisan loremundi:check).
 * Chaque point dit ce qui ne va pas et comment le corriger ; les textes restent en français,
 * comme les autres commandes d'exploitation.
 */
class ProductionCheck
{
    public const ERROR = 'erreur';

    public const WARNING = 'attention';

    /** Clés Reverb livrées dans .env.example : à remplacer en production. */
    private const SAMPLE_REVERB_KEYS = ['codexflow-local', 'codexflow-local-secret'];

    /**
     * @return list<array{label: string, ok: bool, level: string, hint: string}>
     */
    public static function run(): array
    {
        $https = Str::startsWith((string) config('app.url'), 'https://');
        $mailer = (string) config('mail.default');
        $reverb = (array) config('broadcasting.connections.reverb', []);
        $legal = (array) config('codexflow.legal', []);

        return [
            self::item('Environnement de production', app()->isProduction(), self::WARNING, 'APP_ENV=production dans le .env.'),
            self::item('Mode débogage coupé', ! config('app.debug'), self::ERROR, 'APP_DEBUG=false : sinon les erreurs affichent le code et la configuration.'),
            self::item('Clé de chiffrement', filled(config('app.key')), self::ERROR, 'php artisan key:generate (une seule fois : la changer rend illisibles les clés IA enregistrées).'),
            self::item('Adresse en HTTPS', $https, self::ERROR, 'APP_URL=https://votre-domaine : HTTPS est nécessaire à l’application installable et aux notifications push.'),
            self::item('Cookies de session en HTTPS', ! $https || (bool) config('session.secure'), self::ERROR, 'SESSION_SECURE_COOKIE=true.'),
            self::database(),
            self::migrations(),
            self::item('Fichiers de l’interface compilés', is_file(public_path('build/manifest.json')), self::ERROR, 'npm ci && npm run build (fait par le script de déploiement).'),
            self::writable(),
            self::queue(),
            self::item('Envoi d’e-mails', ! in_array($mailer, ['log', 'array'], true), self::ERROR, 'MAIL_MAILER=smtp et les accès SMTP (Brevo) : sinon invitations et mots de passe oubliés ne partent pas.'),
            self::item('Expéditeur des e-mails', ! Str::endsWith((string) config('mail.from.address'), ['.test', '.local', 'example.com']), self::WARNING, 'MAIL_FROM_ADDRESS sur votre domaine, déclaré chez Brevo.'),
            self::item('Temps réel (Reverb)', config('broadcasting.default') === 'reverb' && filled($reverb['key'] ?? null) && ! in_array($reverb['key'] ?? null, self::SAMPLE_REVERB_KEYS, true) && ! in_array($reverb['secret'] ?? null, self::SAMPLE_REVERB_KEYS, true), self::ERROR, 'REVERB_APP_KEY et REVERB_APP_SECRET : des valeurs propres au serveur (Forge les génère en activant Reverb).'),
            self::item('Temps réel chiffré', ! $https || ($reverb['options']['scheme'] ?? null) === 'https', self::WARNING, 'REVERB_SCHEME=https et REVERB_PORT=443 : le navigateur refuse une connexion non chiffrée depuis une page HTTPS.'),
            self::item('Clés des notifications push', PushChannel::enabled(), self::WARNING, 'php artisan webpush:vapid, une seule fois : changer les clés désabonne tous les appareils.'),
            self::item('Extension GMP ou BCMath', SystemHealth::hasBigMath(), PushChannel::enabled() ? self::ERROR : self::WARNING, 'Installer php8.3-gmp (ou php8.3-bcmath) sur le serveur, puis redémarrer PHP.'),
            self::item('Journal sans détails de débogage', ! app()->isProduction() || config('logging.channels.'.config('logging.default').'.level', 'debug') !== 'debug', self::WARNING, 'LOG_LEVEL=warning et LOG_STACK=daily.'),
            self::item('Mentions légales', filled($legal['owner'] ?? null) && filled($legal['email'] ?? null) && filled($legal['host'] ?? null), self::WARNING, 'LEGAL_OWNER, LEGAL_EMAIL, LEGAL_HOST (et LEGAL_SIRET, LEGAL_ADDRESS) dans le .env.'),
            self::item('Sauvegarde de nuit', (bool) config('codexflow.backup.enabled'), self::WARNING, 'BACKUP_ENABLED=true : base et fichiers copiés chaque nuit (php artisan loremundi:backup).'),
        ];
    }

    /** @return list<array{label: string, ok: bool, level: string, hint: string}> */
    public static function errors(): array
    {
        return array_values(array_filter(self::run(), fn (array $item) => ! $item['ok'] && $item['level'] === self::ERROR));
    }

    /** @return array{label: string, ok: bool, level: string, hint: string} */
    private static function item(string $label, bool $ok, string $level, string $hint): array
    {
        return ['label' => $label, 'ok' => $ok, 'level' => $level, 'hint' => $hint];
    }

    /** @return array{label: string, ok: bool, level: string, hint: string} */
    private static function database(): array
    {
        $hint = 'DB_CONNECTION=pgsql (PostgreSQL 16) et les accès DB_* du .env ; l’extension unaccent s’active à la migration.';

        try {
            $ok = DB::connection()->getDriverName() === 'pgsql'
                && DB::selectOne("select 1 as ok from pg_extension where extname = 'unaccent'") !== null;
        } catch (Throwable $e) {
            return self::item('Base PostgreSQL', false, self::ERROR, $hint.' Erreur : '.Str::limit(Str::squish($e->getMessage()), 160));
        }

        return self::item('Base PostgreSQL', $ok, self::ERROR, $hint);
    }

    /** @return array{label: string, ok: bool, level: string, hint: string} */
    private static function migrations(): array
    {
        try {
            /** @var Migrator $migrator */
            $migrator = app('migrator');
            $ran = $migrator->repositoryExists() ? $migrator->getRepository()->getRan() : [];
            $pending = array_diff(array_keys($migrator->getMigrationFiles([database_path('migrations')])), $ran);
        } catch (Throwable) {
            $pending = ['?'];
        }

        return self::item('Base à jour', $pending === [], self::ERROR, 'php artisan migrate --force (fait par le script de déploiement).');
    }

    /** @return array{label: string, ok: bool, level: string, hint: string} */
    private static function writable(): array
    {
        $paths = [storage_path('app/private'), storage_path('framework'), storage_path('logs'), base_path('bootstrap/cache')];
        $blocked = array_filter($paths, fn (string $path) => ! is_dir($path) || ! is_writable($path));

        return self::item('Dossiers inscriptibles', $blocked === [], self::ERROR, 'Le serveur web doit pouvoir écrire dans '.implode(', ', array_map(fn ($path) => Str::after($path, base_path().'/'), $blocked ?: $paths)).'.');
    }

    /** @return array{label: string, ok: bool, level: string, hint: string} */
    private static function queue(): array
    {
        $connection = (string) config('queue.default');

        if ($connection === 'sync') {
            return self::item('File d’attente', true, self::WARNING, '');
        }

        try {
            $ok = $connection !== 'database' || Schema::hasTable((string) config('queue.connections.database.table', 'jobs'));
            $failed = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;
        } catch (Throwable) {
            return self::item('File d’attente', false, self::ERROR, 'La table des tâches est inaccessible : vérifier la base.');
        }

        if ($ok && $failed > 0) {
            return self::item('File d’attente', false, self::WARNING, $failed.' tâche(s) en échec : php artisan queue:failed pour les voir, queue:retry all pour les relancer.');
        }

        return self::item('File d’attente', $ok, self::ERROR, 'php artisan migrate --force, puis le démon « php artisan queue:work » dans Forge.');
    }
}
