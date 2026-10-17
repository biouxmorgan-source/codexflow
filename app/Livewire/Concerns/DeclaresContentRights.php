<?php

namespace App\Livewire\Concerns;

use App\Models\Campaign;
use App\Models\ContentDeclaration;
use Throwable;

/**
 * Déclaration de droits obligatoire avant chaque import (<x-import-declaration>) : vérifiée ici,
 * côté serveur, puis inscrite au registre avec l'issue de l'import. La case se décoche après
 * chaque opération, pour qu'un nouvel import demande une nouvelle acceptation.
 */
trait DeclaresContentRights
{
    public bool $rightsDeclared = false;

    /**
     * Lance $import si la déclaration est cochée. $import renvoie false (échec prévu, déjà signalé)
     * ou n'importe quoi d'autre en cas de réussite ; une Campaign renvoyée est associée à la trace.
     */
    protected function withDeclaredRights(string $importType, ?Campaign $campaign, callable $import): mixed
    {
        $this->validate(
            ['rightsDeclared' => ['accepted']],
            ['rightsDeclared.accepted' => __('Cochez la déclaration de droits pour importer.')],
        );

        $declaration = ContentDeclaration::accept(auth()->user(), $importType, $campaign);
        $this->rightsDeclared = false;

        try {
            $result = $import();
        } catch (Throwable $e) {
            $declaration->finish(false);

            throw $e;
        }

        $declaration->finish($result !== false, $result instanceof Campaign ? $result : null);

        return $result;
    }
}
