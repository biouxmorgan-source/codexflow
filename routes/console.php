<?php

use App\Actions\Demo\LoadDemoCampaign;
use App\Models\User;
use App\Support\Backup;
use App\Support\Locale;
use App\Support\ProductionCheck;
use App\Support\RecetteImport;
use App\Support\TranslationKeys;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('lang:missing {locale?}', function (?string $locale = null) {
    $locales = $locale ? [$locale] : array_diff(array_keys(Locale::available()), [Locale::DEFAULT]);

    foreach ($locales as $code) {
        $missing = TranslationKeys::missing($code);
        $this->line($code.' : '.count($missing).' texte(s) sans traduction');

        foreach ($missing as $key) {
            $this->line('  '.$key);
        }
    }
})->purpose('Liste les textes de l\'interface sans traduction');

Artisan::command('codexflow:admin {email} {--remove}', function (string $email) {
    $user = User::where('email', $email)->first();

    if ($user === null) {
        $this->error('Aucun compte avec cette adresse.');

        return 1;
    }

    $user->forceFill(['is_admin' => ! $this->option('remove')])->save();
    $this->info($user->is_admin ? $user->name.' administre LoreMundi.' : $user->name.' n\'administre plus LoreMundi.');
})->purpose('Donne (ou retire avec --remove) le rôle d\'administrateur à un compte');

Artisan::command('codexflow:demo {email} {--lang=fr : langue du contenu}', function (string $email, LoadDemoCampaign $loadDemo) {
    $user = User::where('email', $email)->first();

    if ($user === null) {
        $this->error('Aucun compte avec cette adresse.');

        return 1;
    }

    if (! in_array($this->option('lang'), LoadDemoCampaign::locales(), true)) {
        $this->error('Langues disponibles : '.implode(', ', LoadDemoCampaign::locales()));

        return 1;
    }

    $campaign = $loadDemo->handle($user, $this->option('lang'));
    $this->info('Campagne de démonstration « '.$campaign->name.' » chargée dans le compte de '.$user->name.'.');
})->purpose('Charge la campagne de démonstration dans un compte');

Artisan::command('codexflow:recettes', function () {
    $added = RecetteImport::all();
    $this->info($added === [] ? 'Aucun nouveau cahier de recette.' : 'Cahiers ajoutés : '.implode(', ', $added));
})->purpose('Ajoute à la console d\'administration les cahiers de recette livrés (et leurs suites au backlog)');

Artisan::command('loremundi:check', function () {
    $errors = 0;

    foreach (ProductionCheck::run() as $item) {
        if ($item['ok']) {
            $this->line('<info>✓</info> '.$item['label']);

            continue;
        }

        $error = $item['level'] === ProductionCheck::ERROR;
        $errors += $error ? 1 : 0;
        $this->line(($error ? '<error>✗ ' : '<comment>! ').$item['label'].($error ? '</error>' : '</comment>'));
        $this->line('    '.$item['hint']);
    }

    $this->newLine();
    $errors === 0
        ? $this->info('Aucun point bloquant.')
        : $this->error($errors.' point(s) bloquant(s) à corriger.');

    return $errors === 0 ? 0 : 1;
})->purpose('Vérifie qu\'une installation est prête pour la production');

Artisan::command('loremundi:backup {--path= : dossier des sauvegardes} {--keep= : jours de conservation}', function () {
    try {
        $target = Backup::run($this->option('path') ?: null, $this->option('keep') !== null ? (int) $this->option('keep') : null);
    } catch (RuntimeException $e) {
        $this->error($e->getMessage());

        return 1;
    }

    $this->info('Sauvegarde écrite dans '.$target);
})->purpose('Sauvegarde la base et les fichiers envoyés dans un dossier daté');

// Tâches régulières (cron : php artisan schedule:run chaque minute).
Schedule::command('model:prune')->daily();

if (config('codexflow.backup.enabled')) {
    Schedule::command('loremundi:backup')->dailyAt('03:30')->withoutOverlapping()->onOneServer();
}
