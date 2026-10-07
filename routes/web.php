<?php

use App\Http\Controllers\ExportController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ImportExampleController;
use App\Livewire\Campaigns\Index as CampaignIndex;
use App\Livewire\Campaigns\Show as CampaignShow;
use App\Livewire\Documents\Index as DocumentIndex;
use App\Livewire\Documents\Show as DocumentShow;
use App\Livewire\Entities\Form as EntityForm;
use App\Livewire\Entities\Show as EntityShow;
use App\Livewire\EntityTypes\Manage as EntityTypesManage;
use App\Livewire\Fields\Manage as FieldsManage;
use App\Livewire\Imports\Create as ImportCreate;
use App\Livewire\Rules\Form as RuleForm;
use App\Livewire\Rules\Index as RuleIndex;
use App\Livewire\Rules\Show as RuleShow;
use App\Livewire\Scenarios\Index as ScenarioIndex;
use App\Livewire\Scenes\Form as SceneForm;
use App\Livewire\Scenes\Show as SceneShow;
use App\Livewire\Search\Index as SearchIndex;
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

    Route::livewire('/campagnes/{campaign}/regles', RuleIndex::class)->name('rules.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/regles/nouvelle', RuleForm::class)->name('rules.create')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/regles/{rule}', RuleShow::class)->name('rules.show')->whereNumber(['campaign', 'rule']);
    Route::livewire('/campagnes/{campaign}/regles/{rule}/modifier', RuleForm::class)->name('rules.edit')->whereNumber(['campaign', 'rule']);
    Route::livewire('/campagnes/{campaign}/documents', DocumentIndex::class)->name('documents.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/documents/{document}', DocumentShow::class)->name('documents.show')->whereNumber(['campaign', 'document']);

    Route::livewire('/campagnes/{campaign}/recherche', SearchIndex::class)->name('search.index')->whereNumber('campaign');

    Route::livewire('/campagnes/{campaign}/champs', FieldsManage::class)->name('fields.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/import', ImportCreate::class)->name('imports.create')->whereNumber('campaign');
    Route::get('/campagnes/{campaign}/import/exemple-{kind}.csv', ImportExampleController::class)->name('imports.example')->whereNumber('campaign')->whereIn('kind', ['fiches', 'champs', 'regles', 'scenes']);
    Route::get('/campagnes/{campaign}/export/{kind}.csv', ExportController::class)->name('exports.download')->whereNumber('campaign')->whereIn('kind', ['fiches', 'champs', 'regles', 'scenes']);

    Route::get('/fichiers/{attachment}', [FileController::class, 'attachment'])->name('attachments.show')->whereNumber('attachment');
    Route::get('/documents/{document}/fichier', [FileController::class, 'document'])->name('documents.file')->whereNumber('document');
    Route::get('/entites/{entity}/image', [FileController::class, 'entityImage'])->name('entities.image')->whereNumber('entity');
});
