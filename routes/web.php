<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\ImportExampleController;
use App\Livewire\Campaigns\Index as CampaignIndex;
use App\Livewire\Campaigns\Show as CampaignShow;
use App\Livewire\Entities\Form as EntityForm;
use App\Livewire\Entities\Show as EntityShow;
use App\Livewire\EntityTypes\Manage as EntityTypesManage;
use App\Livewire\Fields\Manage as FieldsManage;
use App\Livewire\Imports\Create as ImportCreate;
use App\Livewire\Scenarios\Index as ScenarioIndex;
use App\Livewire\Scenes\Form as SceneForm;
use App\Livewire\Scenes\Show as SceneShow;
use App\Livewire\Sessions\Live as SessionLive;
use App\Livewire\Sessions\Show as SessionShow;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'campaigns.index' : 'login'));

Route::middleware('auth')->group(function () {
    Route::livewire('/campagnes', CampaignIndex::class)->name('campaigns.index');
    Route::livewire('/types-de-fiche', EntityTypesManage::class)->name('entity-types.index');
    Route::livewire('/campagnes/{campaign}', CampaignShow::class)->name('campaigns.show')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/entites/nouvelle', EntityForm::class)->name('entities.create')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/entites/{entity}', EntityShow::class)->name('entities.show')->whereNumber(['campaign', 'entity']);
    Route::livewire('/campagnes/{campaign}/entites/{entity}/modifier', EntityForm::class)->name('entities.edit')->whereNumber(['campaign', 'entity']);
    Route::livewire('/campagnes/{campaign}/scenarios', ScenarioIndex::class)->name('scenarios.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/scenes/nouvelle', SceneForm::class)->name('scenes.create')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/scenes/{scene}', SceneShow::class)->name('scenes.show')->whereNumber(['campaign', 'scene']);
    Route::livewire('/campagnes/{campaign}/scenes/{scene}/modifier', SceneForm::class)->name('scenes.edit')->whereNumber(['campaign', 'scene']);

    Route::livewire('/campagnes/{campaign}/session', SessionLive::class)->name('sessions.live')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/sessions/{playSession}', SessionShow::class)->name('sessions.show')->whereNumber(['campaign', 'playSession']);

    Route::livewire('/campagnes/{campaign}/champs', FieldsManage::class)->name('fields.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/import', ImportCreate::class)->name('imports.create')->whereNumber('campaign');
    Route::get('/campagnes/{campaign}/import/exemple-{kind}.csv', ImportExampleController::class)->name('imports.example')->whereNumber('campaign')->whereIn('kind', ['fiches', 'champs']);

    Route::get('/fichiers/{attachment}', [FileController::class, 'attachment'])->name('attachments.show')->whereNumber('attachment');
    Route::get('/entites/{entity}/image', [FileController::class, 'entityImage'])->name('entities.image')->whereNumber('entity');
});
