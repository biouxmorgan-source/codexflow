<?php

use App\Actions\Demo\LoadDemoCampaign;
use App\Models\User;
use App\Support\Locale;
use App\Support\TranslationKeys;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

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
    $this->info($user->is_admin ? $user->name.' administre CodexFlow.' : $user->name.' n\'administre plus CodexFlow.');
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
