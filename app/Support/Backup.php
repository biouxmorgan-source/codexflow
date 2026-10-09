<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * Sauvegarde de l'installation (php artisan loremundi:backup) : la base en format pg_dump
 * personnalisé et les fichiers envoyés en archive tar.gz, dans un dossier daté. Les dossiers
 * plus vieux que keep_days sont supprimés. Restauration : docs/mise-en-ligne.md.
 */
class Backup
{
    /** @return string dossier de la sauvegarde créée */
    public static function run(?string $path = null, ?int $keepDays = null): string
    {
        $root = rtrim($path ?? (string) config('codexflow.backup.path'), '/');
        $target = $root.'/'.now()->format('Y-m-d_His');
        File::ensureDirectoryExists($target, 0700);

        $db = (array) config('database.connections.'.config('database.default'));
        if (($db['driver'] ?? null) !== 'pgsql') {
            throw new RuntimeException('La sauvegarde ne connaît que PostgreSQL.');
        }

        $dump = Process::env(['PGPASSWORD' => (string) ($db['password'] ?? '')])->timeout(3600)->run([
            'pg_dump', '--format=custom', '--no-owner', '--no-privileges',
            '--host='.$db['host'], '--port='.$db['port'], '--username='.$db['username'],
            '--file='.$target.'/database.dump', (string) $db['database'],
        ]);
        if ($dump->failed()) {
            throw new RuntimeException('pg_dump a échoué : '.trim($dump->errorOutput()));
        }

        $files = storage_path('app/private');
        if (is_dir($files)) {
            $tar = Process::timeout(3600)->run(['tar', '-czf', $target.'/files.tar.gz', '-C', $files, '.']);
            if ($tar->failed()) {
                throw new RuntimeException('L’archive des fichiers a échoué : '.trim($tar->errorOutput()));
            }
        }

        self::prune($root, $keepDays ?? (int) config('codexflow.backup.keep_days', 7), $target);

        return $target;
    }

    /** Supprime les sauvegardes datées plus anciennes que $keepDays jours (jamais celle du jour). */
    public static function prune(string $root, int $keepDays, ?string $keep = null): void
    {
        $limit = now()->subDays(max(1, $keepDays))->format('Y-m-d_His');

        foreach (File::directories($root) as $dir) {
            $name = basename($dir);
            if ($dir !== $keep && preg_match('/^\d{4}-\d{2}-\d{2}_\d{6}$/', $name) && $name < $limit) {
                File::deleteDirectory($dir);
            }
        }
    }
}
