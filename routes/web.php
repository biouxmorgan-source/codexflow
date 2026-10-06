<?php

use App\Livewire\Campaigns\Index as CampaignIndex;
use App\Livewire\Campaigns\Show as CampaignShow;
use App\Livewire\Entities\Form as EntityForm;
use App\Livewire\Entities\Show as EntityShow;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'campaigns.index' : 'login'));

Route::middleware('auth')->group(function () {
    Route::livewire('/campagnes', CampaignIndex::class)->name('campaigns.index');
    Route::livewire('/campagnes/{campaign}', CampaignShow::class)->name('campaigns.show')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/entites/nouvelle', EntityForm::class)->name('entities.create')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/entites/{entity}', EntityShow::class)->name('entities.show')->whereNumber(['campaign', 'entity']);
    Route::livewire('/campagnes/{campaign}/entites/{entity}/modifier', EntityForm::class)->name('entities.edit')->whereNumber(['campaign', 'entity']);
});
