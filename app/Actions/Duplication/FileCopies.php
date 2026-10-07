<?php

namespace App\Actions\Duplication;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Fichiers copiés pendant une duplication.
 *
 * Les copies sont faites pendant la transaction, au moment où l'on crée la ligne qui les porte :
 * le chemin de la copie doit être connu pour enregistrer la ligne. Si la transaction échoue,
 * les lignes disparaissent avec le rollback et run() efface les fichiers déjà copiés,
 * pour ne laisser aucun fichier orphelin sur le disque.
 */
final class FileCopies
{
    /** @var list<array{0: string, 1: string}> [disque, chemin] */
    private array $copied = [];

    /**
     * Exécute $callback avec un registre de copies, nettoyé si le callback échoue.
     *
     * @template T
     *
     * @param  callable(self): T  $callback
     * @return T
     */
    public static function run(callable $callback): mixed
    {
        $files = new self;

        try {
            return $callback($files);
        } catch (Throwable $e) {
            $files->discard();

            throw $e;
        }
    }

    /**
     * Copie un fichier sous un nouveau nom aléatoire dans $directory.
     * Renvoie null si le fichier d'origine n'existe plus.
     */
    public function copy(string $disk, ?string $path, string $directory): ?string
    {
        if ($path === null || ! Storage::disk($disk)->exists($path)) {
            return null;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $target = trim($directory, '/').'/'.Str::random(40).($extension !== '' ? '.'.$extension : '');
        Storage::disk($disk)->copy($path, $target);
        $this->copied[] = [$disk, $target];

        return $target;
    }

    public function discard(): void
    {
        foreach ($this->copied as [$disk, $path]) {
            Storage::disk($disk)->delete($path);
        }

        $this->copied = [];
    }
}
