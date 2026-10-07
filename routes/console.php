<?php

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
