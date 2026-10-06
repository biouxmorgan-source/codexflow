<?php

use App\Livewire\Campaigns\Index as CampaignIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'campaigns.index' : 'login'));

Route::middleware('auth')->group(function () {
    Route::livewire('/campagnes', CampaignIndex::class)->name('campaigns.index');
});
