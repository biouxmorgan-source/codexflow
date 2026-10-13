<?php

namespace App\Support\Plans;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Formules des comptes : administrateur (aucune limite), premium, essai et gratuite.
 * Quotas et fonctions de la formule gratuite se règlent dans la console d'administration.
 * Les fonctions d'une campagne suivent la formule de son propriétaire : un joueur invité
 * profite de ce que permet le compte de son MJ.
 */
class Plans
{
    public const ADMIN = 'admin';

    public const PREMIUM = 'premium';

    public const FREE = 'free';

    /** Essai : un compte gratuit récent a toutes les fonctions pendant quelques semaines. */
    public const TRIAL = 'trial';

    /**
     * Démo : formule des comptes créés pendant le mode démo (avant le Kickstarter). Une copie
     * verrouillée de la campagne de démonstration, une petite campagne à soi, pas d'essai ;
     * l'administrateur débloque le compte à la main.
     */
    public const DEMO = 'demo';

    /** Formules qu'un administrateur attribue (l'administration est un rôle à part). */
    public const ASSIGNABLE = [self::FREE, self::PREMIUM, self::DEMO];

    /** Fonctions que la formule gratuite peut perdre. */
    public const FEATURES = ['ai', 'table', 'maps', 'duplication', 'archive'];

    /** Message d'une page refusée faute de formule (traduit par la page d'erreur). */
    public const NOT_INCLUDED = 'Cette fonction n’est pas comprise dans la formule de cette campagne.';

    /** Invitation refusée : la campagne d'un compte démo a déjà tous ses membres (traduit par la page d'erreur). */
    public const DEMO_FULL = 'Cette campagne de démonstration a déjà tous ses joueurs.';

    public const DEFAULTS = [
        'free_storage_mb' => 250,
        'premium_storage_mb' => 2048,
        // Espace ajouté à la formule des Fondateurs (contributeurs du Kickstarter), quelle qu'elle soit.
        'founder_bonus_mb' => 500,
        'free_max_campaigns' => 1,
        'free_features' => self::FEATURES,
        'trial_weeks' => 6,
        // Période offerte (Noël…) : fonctions Premium pour les campagnes des comptes gratuits, dates incluses.
        'gift_starts_on' => null,
        'gift_ends_on' => null,
        // Mode démo : chaque nouveau compte reçoit la formule Démo, sauf invitation d'un administrateur.
        'demo_mode' => false,
        'demo_storage_mb' => 50,
        'demo_max_campaigns' => 1,
        'demo_max_players' => 3,
        'demo_features' => ['table', 'maps'],
    ];

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [self::ADMIN => __('Administrateur'), self::PREMIUM => __('Premium'), self::TRIAL => __('Essai'), self::FREE => __('Gratuit'), self::DEMO => __('Démo')];
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

    /** @return array{free_storage_mb: int, premium_storage_mb: int, founder_bonus_mb: int, free_max_campaigns: int, free_features: list<string>, trial_weeks: int, gift_starts_on: ?string, gift_ends_on: ?string, demo_mode: bool, demo_storage_mb: int, demo_max_campaigns: int, demo_max_players: int, demo_features: list<string>} */
    public static function settings(): array
    {
        $saved = Setting::get('plans', []);

        return array_merge(self::DEFAULTS, is_array($saved) ? array_intersect_key($saved, self::DEFAULTS) : []);
    }

    public static function save(array $settings): void
    {
        Setting::put('plans', array_intersect_key($settings, self::DEFAULTS) + self::settings());
    }

    /**
     * Formule en vigueur : une formule premium échue redevient gratuite ;
     * un compte gratuit encore dans sa période d'essai a tout ce qu'a le premium.
     */
    public static function effective(User $user): string
    {
        if ($user->is_admin) {
            return self::ADMIN;
        }

        // Un compte démo n'entame pas son essai : il commence quand l'administrateur le débloque.
        if ($user->plan === self::DEMO) {
            return self::DEMO;
        }

        $active = $user->plan === self::PREMIUM && ($user->plan_ends_at === null || ! $user->plan_ends_at->isPast() || $user->plan_ends_at->isToday());

        if ($active) {
            return self::PREMIUM;
        }

        return self::trialEndsAt($user)?->isFuture() ? self::TRIAL : self::FREE;
    }

    /**
     * Fin de l'essai, comptée depuis la première campagne possédée : un joueur qui n'a
     * jamais été MJ garde son essai entier. Null tant qu'il n'a pas commencé ou s'il est désactivé.
     */
    public static function trialEndsAt(User $user): ?Carbon
    {
        $weeks = self::trialWeeks();

        return $weeks > 0 && $user->trial_started_at ? $user->trial_started_at->copy()->addWeeks($weeks) : null;
    }

    public static function trialWeeks(): int
    {
        return (int) self::settings()['trial_weeks'];
    }

    /**
     * Espace de stockage permis, en octets ; null = sans limite. Un Fondateur a en plus son
     * bonus, sauf si l'administrateur lui a fixé un stockage propre.
     */
    public static function storageLimit(User $user): ?int
    {
        $plan = self::effective($user);

        if ($plan === self::ADMIN && $user->storage_quota_mb === null) {
            return null;
        }

        $settings = self::settings();
        $mb = $user->storage_quota_mb ?? match ($plan) {
            self::DEMO => $settings['demo_storage_mb'],
            self::FREE => $settings['free_storage_mb'] + ($user->isFounder() ? $settings['founder_bonus_mb'] : 0),
            default => $settings['premium_storage_mb'] + ($user->isFounder() ? $settings['founder_bonus_mb'] : 0),
        };

        return (int) $mb * 1024 * 1024;
    }

    /**
     * Campagnes que le compte peut posséder (comme MJ propriétaire) ; null = sans limite.
     * En démo, la copie de la campagne de démonstration ne compte pas.
     */
    public static function maxCampaigns(User $user): ?int
    {
        return match (self::effective($user)) {
            self::FREE => (int) self::settings()['free_max_campaigns'],
            self::DEMO => (int) self::settings()['demo_max_campaigns'],
            default => null,
        };
    }

    /** Refuse une action dont la fonction n'est pas comprise dans la formule. */
    public static function ensure(User $owner, string $feature): void
    {
        abort_unless(self::allows($owner, $feature), 403, self::NOT_INCLUDED);
    }

    /** Fonction réservée aux formules payantes (hors de l'offre gratuite choisie dans la console) : marquée ✦. */
    public static function isPremium(string $feature): bool
    {
        return in_array($feature, self::FEATURES, true) && ! in_array($feature, self::settings()['free_features'], true);
    }

    public static function allows(User $owner, string $feature): bool
    {
        // Les fonctions hors formule (graphe, chronologie…) sont comprises partout.
        if (! in_array($feature, self::FEATURES, true)) {
            return true;
        }

        return match (self::effective($owner)) {
            self::DEMO => in_array($feature, self::settings()['demo_features'], true),
            self::FREE => self::giftActive() || in_array($feature, self::settings()['free_features'], true),
            default => true,
        };
    }

    public static function demoMode(): bool
    {
        return (bool) self::settings()['demo_mode'];
    }

    /** Membres que peut inviter un compte démo dans sa campagne, en plus de lui ; null = sans limite. */
    public static function maxMembers(User $owner): ?int
    {
        return self::effective($owner) === self::DEMO ? (int) self::settings()['demo_max_players'] : null;
    }

    /**
     * Sort un compte de la démo : sa formule devient celle donnée et, puisqu'il possède déjà sa
     * copie de démonstration, son essai commence maintenant.
     */
    public static function leaveDemo(User $user, string $plan = self::FREE): void
    {
        if ($user->plan !== self::DEMO) {
            return;
        }

        $user->forceFill([
            'plan' => $plan,
            'trial_started_at' => $user->trial_started_at ?? ($user->ownedCampaigns()->exists() ? now() : null),
        ])->save();
    }

    /**
     * Période offerte en cours : seulement les fonctions. Le nombre de campagnes et le stockage
     * restent ceux de l'offre gratuite, pour qu'aucun compte ne se retrouve au-delà à la fin.
     */
    public static function giftActive(): bool
    {
        ['gift_starts_on' => $start, 'gift_ends_on' => $end] = self::settings();

        return $start !== null && $end !== null && today()->betweenIncluded(Carbon::parse($start), Carbon::parse($end));
    }

    /** Avant de créer, importer, dupliquer ou charger une campagne. */
    public static function ensureCanCreateCampaign(User $user, string $field = 'plan'): void
    {
        $max = self::maxCampaigns($user);

        $owned = $user->ownedCampaigns()->when(self::effective($user) === self::DEMO, fn ($query) => $query->where('is_demo', false))->count();

        if ($max !== null && $owned >= $max) {
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
