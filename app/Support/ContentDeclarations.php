<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Registre versionné des déclarations de droits que l'utilisateur accepte avant d'importer
 * (et, plus tard, avant de publier). Un texte accepté ne se modifie jamais : pour le changer,
 * ajouter une nouvelle version datée ; la dernière de chaque liste est celle affichée.
 * Le texte français fait foi ; les autres langues viennent de lang/{code}.json.
 */
class ContentDeclarations
{
    public const VERSIONS = [
        'import' => [
            '2026-10-17' => [
                'Je certifie disposer des droits, licences ou autorisations nécessaires à l’importation, au stockage et à l’utilisation des contenus que je souhaite intégrer dans SagaWyn, ou bénéficier d’une exception légale applicable.',
                'Je reconnais être responsable des contenus importés et de leur utilisation, notamment de leur éventuel partage avec d’autres utilisateurs, dans le respect des droits des tiers.',
                'Je reconnais que SagaWyn fournit un service technique d’hébergement et d’organisation des données, sans validation préalable des droits attachés aux contenus importés.',
            ],
        ],
        // Pour une future bibliothèque publique : déclaration distincte de l'import privé.
        'publication' => [
            '2026-10-17' => [
                'Je certifie disposer des droits et autorisations nécessaires à la diffusion publique des contenus que je souhaite publier, et m’engage à respecter les droits de propriété intellectuelle des tiers.',
            ],
        ],
    ];

    public static function current(string $kind = 'import'): string
    {
        return array_key_last(self::versions($kind));
    }

    /** @return list<string> Le texte français exact de cette version. */
    public static function text(string $kind = 'import', ?string $version = null): array
    {
        return self::versions($kind)[$version ?? self::current($kind)]
            ?? throw new InvalidArgumentException("Version inconnue : {$kind} {$version}");
    }

    /** @return array<string, list<string>> */
    private static function versions(string $kind): array
    {
        return self::VERSIONS[$kind] ?? throw new InvalidArgumentException("Déclaration inconnue : {$kind}");
    }
}
