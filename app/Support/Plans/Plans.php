<?php

namespace App\Support\Plans;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Formules des comptes : administrateur (aucune limite), premium et gratuite.
 * Quotas et fonctions de la formule gratuite se règlent dans la console d'administration.
 * Les fonctions d'une campagne suivent la formule de son propriétaire : un joueur invité
 * profite de ce que permet le compte de son MJ.
 */
class Plans
{
    public const ADMIN = 'admin';

    public const PREMIUM = 'premium';

    public const FREE = 'free';

    /** Formules qu'un administrateur attribue (l'administration est un rôle à part). */
    public const ASSIGNABLE = [self::FREE, self::PREMIUM];

    /** Fonctions que la formule gratuite peut perdre. */
    public const FEATURES = ['ai', 'table', 'maps', 'duplication', 'archive'];

    /** Message d'une page refusée faute de formule (traduit par la page d'erreur). */
    public const NOT_INCLUDED = 'Cette fonction n’est pas comprise dans la formule de cette campagne.';

    public const DEFAULTS = [
        'free_storage_mb' => 250,
        'premium_storage_mb' => 2048,
        'free_max_campaigns' => 1,
        'free_features' => self::FEATURES,
    ];

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [self::ADMIN => __('Administrateur'), self::PREMIUM => __('Premium'), self::FREE => __('Gratuit')];
    }

    /** @return array<string, string> */
    public static function features(): array
    {
        return [
            'ai' => __('Assistant IA'),
            'table' => __('Écran de table et télécommande'),
            'maps' => __('Cartes et jetons'),
            'duplication' => __('Duplication (campagne, scénario, fiche)'),
            'archive' => __('Export et import de campagnes et de modèles'),
        ];
    }

    /** @return array{free_storage_mb: int, premium_storage_mb: int, free_max_campaigns: int, free_features: list<string>} */
    public static function settings(): array
    {
        $saved = Setting::get('plans', []);

        return array_merge(self::DEFAULTS, is_array($saved) ? array_intersect_key($saved, self::DEFAULTS) : []);
    }

    public static function save(array $settings): void
    {
        Setting::put('plans', array_intersect_key($settings, self::DEFAULTS) + self::settings());
    }

    /** Formule en vigueur : une formule premium échue redevient gratuite. */
    public static function effective(User $user): string
    {
        if ($user->is_admin) {
            return self::ADMIN;
        }

        $active = $user->plan === self::PREMIUM && ($user->plan_ends_at === null || ! $user->plan_ends_at->isPast() || $user->plan_ends_at->isToday());

        return $active ? self::PREMIUM : self::FREE;
    }

    /** Espace de stockage permis, en octets ; null = sans limite. */
    public static function storageLimit(User $user): ?int
    {
        $plan = self::effective($user);

        if ($plan === self::ADMIN && $user->storage_quota_mb === null) {
            return null;
        }

        $mb = $user->storage_quota_mb ?? self::settings()[$plan === self::PREMIUM ? 'premium_storage_mb' : 'free_storage_mb'];

        return (int) $mb * 1024 * 1024;
    }

    /** Campagnes que le compte peut posséder (comme MJ propriétaire) ; null = sans limite. */
    public static function maxCampaigns(User $user): ?int
    {
        return self::effective($user) === self::FREE ? (int) self::settings()['free_max_campaigns'] : null;
    }

    /** Refuse une action dont la fonction n'est pas comprise dans la formule. */
    public static function ensure(User $owner, string $feature): void
    {
        abort_unless(self::allows($owner, $feature), 403, self::NOT_INCLUDED);
    }

    public static function allows(User $owner, string $feature): bool
    {
        return self::effective($owner) !== self::FREE || in_array($feature, self::settings()['free_features'], true);
    }

    /** Avant de créer, importer, dupliquer ou charger une campagne. */
    public static function ensureCanCreateCampaign(User $user, string $field = 'plan'): void
    {
        $max = self::maxCampaigns($user);

        if ($max !== null && $user->ownedCampaigns()->count() >= $max) {
            throw ValidationException::withMessages([$field => trans_choice('Votre formule permet :count campagne en tant que MJ.|Votre formule permet :count campagnes en tant que MJ.', $max)]);
        }
    }

    /** Avant d'enregistrer un fichier : le propriétaire du contenu a-t-il encore la place ? */
    public static function ensureRoom(User $owner, int $bytes, string $field): void
    {
        $limit = self::storageLimit($owner);

        if ($limit === null) {
            return;
        }

        $used = StorageUsage::bytes($owner, fresh: true);

        if ($used + $bytes > $limit) {
            throw ValidationException::withMessages([$field => __('Espace de stockage insuffisant : :used utilisés sur :limit.', [
                'used' => StorageUsage::format($used),
                'limit' => StorageUsage::format($limit),
            ])]);
        }
    }
}
