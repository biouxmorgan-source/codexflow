<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Fichiers CSV d'exemple pour l'import (séparateur « ; » et BOM UTF-8, lisibles par Excel en français).
 */
class ImportExampleController extends Controller
{
    private const EXAMPLES = [
        'fiches' => [
            ['Nom', 'Type', 'Résumé', 'Force', 'Agilité', 'Discrétion', 'Notes MJ'],
            ['Shen Chu', 'Personnage', 'Herboriste du village', '2', '4', 'oui', 'Espionne pour le seigneur local'],
            ['Loup des cendres', 'Créature', 'Prédateur des plaines brûlées', '5', '3', 'non', ''],
        ],
        'champs' => [
            ['Nom', 'Groupe', 'Type', 'Zone', 'Choix', 'Type de fiche'],
            ['Force', 'Caractéristiques', 'nombre', 'publique', '', 'Personnage'],
            ['Agilité', 'Caractéristiques', 'nombre', 'publique', '', 'Personnage'],
            ['Dé de vie', 'Caractéristiques', 'liste', 'publique', 'd4|d6|d8|d10|d12', ''],
            ['Discrétion', 'Compétences', 'oui/non', 'publique', '', ''],
            ['Pouvoir caché', 'Capacités', 'texte long', 'MJ', '', ''],
        ],
        'regles' => [
            ['Titre', 'Catégorie', 'Résumé', 'Procédure', 'Notes MJ', 'Source', 'Origine', 'Statut', 'Zone', 'Tags'],
            ['Voyage', 'Déplacements', 'Un test par jour de marche', "Chaque voyageur lance 1d20.\nEn cas d'échec, il perd 1 point de vigueur.", '', 'Livre de base p. 42', 'référence', 'disponible', 'publique', 'voyage, extérieur'],
            ['Initiative en groupe', 'Combat', 'Un seul jet par camp', '', 'À tester sur deux séances', '', 'maison', 'à tester', 'MJ', 'combat'],
        ],
        'scenes' => [
            ['Scénario', 'Résumé du scénario', 'Chapitre', 'Scène', 'Description', 'Statut', 'Fiches', 'Documents', 'Règles'],
            ['Une nuit au Poney fringant', 'Les héros font halte dans une auberge où rôde un espion.', 'Acte 1', "Arrivée à l'auberge", "La salle est pleine. L'aubergiste propose la dernière chambre.", 'prévue', 'Shen Chu | Le Poney fringant', 'Plan de l\'auberge', ''],
            ['Une nuit au Poney fringant', '', 'Acte 1', 'Visite nocturne', 'Un rôdeur fouille les bagages pendant la nuit.', 'prévue', 'Loup des cendres', '', 'Initiative en groupe'],
        ],
    ];

    public function __invoke(Campaign $campaign, string $kind): StreamedResponse
    {
        Gate::authorize('update', $campaign);
        abort_unless(isset(self::EXAMPLES[$kind]), 404);

        return response()->streamDownload(function () use ($kind) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            foreach (self::EXAMPLES[$kind] as $row) {
                fputcsv($out, $row, ';', '"', '');
            }

            fclose($out);
        }, 'codexflow-exemple-'.$kind.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
