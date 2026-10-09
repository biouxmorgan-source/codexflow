<?php

namespace App\Providers;

use App\Http\Middleware\EnsureFeature;
use App\Models\Campaign;
use App\Models\User;
use App\Models\UserLogin;
use App\Support\Plans\Plans;
use App\Support\RechecksAuthorization;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Mots de passe : 10 caractères avec lettres et chiffres ; en production, refus de ceux
        // déjà apparus dans une fuite connue (vérifié sans envoyer le mot de passe).
        Password::defaults(fn () => app()->isProduction()
            ? Password::min(10)->letters()->numbers()->uncompromised()
            : Password::min(10)->letters()->numbers());

        // Administration de l'installation : lecture des problèmes signalés.
        Gate::define('admin', fn (User $user) => $user->is_admin);

        // Fonction comprise dans la formule : celle du propriétaire de la campagne, sinon la sienne.
        // Formule du propriétaire seulement : une fonction coupée par le MJ se teste avec CampaignFeatures::enabled().
        Gate::define('use-feature', fn (User $user, string $feature, ?Campaign $campaign = null) => Plans::allows($campaign?->owner ?? $user, $feature));

        // Droits revérifiés à chaque requête Livewire, pas seulement à l'ouverture de la page.
        RechecksAuthorization::register();
        Livewire::addPersistentMiddleware([EnsureFeature::class]);

        // Indicateur de la console d'administration : la date de connexion, rien d'autre.
        Event::listen(Login::class, function (Login $event) {
            // Requêtes parallèles reconnectées par « Se souvenir de moi » : une seule connexion par minute.
            if ($event->user instanceof User && ! UserLogin::where('user_id', $event->user->id)->where('logged_in_at', '>=', now()->subMinute())->exists()) {
                UserLogin::create(['user_id' => $event->user->id, 'logged_in_at' => now()]);
            }
        });
    }
}
