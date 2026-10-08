<?php

namespace App\Providers;

use App\Models\Campaign;
use App\Models\User;
use App\Models\UserLogin;
use App\Support\Plans\Plans;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        // Administration de l'installation : lecture des problèmes signalés.
        Gate::define('admin', fn (User $user) => $user->is_admin);

        // Fonction comprise dans la formule : celle du propriétaire de la campagne, sinon la sienne.
        Gate::define('use-feature', fn (User $user, string $feature, ?Campaign $campaign = null) => Plans::allows($campaign?->owner ?? $user, $feature));

        // Indicateur de la console d'administration : la date de connexion, rien d'autre.
        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof User) {
                UserLogin::create(['user_id' => $event->user->id, 'logged_in_at' => now()]);
            }
        });
    }
}
