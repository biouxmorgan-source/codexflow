<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CharacterKnowledgeController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ImportExampleController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\TableScreenController;
use App\Livewire\Account\Preferences;
use App\Livewire\Admin\Backlog as AdminBacklog;
use App\Livewire\Admin\Evolutions as AdminEvolutions;
use App\Livewire\Admin\PlanSettings as AdminPlans;
use App\Livewire\Admin\Recettes as AdminRecettes;
use App\Livewire\Admin\Users as AdminUsers;
use App\Livewire\Ai\Index as AiIndex;
use App\Livewire\Campaigns\Index as CampaignIndex;
use App\Livewire\Campaigns\Show as CampaignShow;
use App\Livewire\Characters\Index as CharacterIndex;
use App\Livewire\Characters\Show as CharacterShow;
use App\Livewire\Documents\Index as DocumentIndex;
use App\Livewire\Documents\Show as DocumentShow;
use App\Livewire\Entities\Form as EntityForm;
use App\Livewire\Entities\Show as EntityShow;
use App\Livewire\EntityTypes\Manage as EntityTypesManage;
use App\Livewire\Fields\Manage as FieldsManage;
use App\Livewire\Graph\Index as GraphIndex;
use App\Livewire\Imports\Create as ImportCreate;
use App\Livewire\Journal\Index as JournalIndex;
use App\Livewire\Maps\Index as MapIndex;
use App\Livewire\Maps\Show as MapShow;
use App\Livewire\Members\Index as MemberIndex;
use App\Livewire\Messages\Index as MessageIndex;
use App\Livewire\Notifications\Index as NotificationIndex;
use App\Livewire\Reveals\Index as RevealIndex;
use App\Livewire\Rules\Form as RuleForm;
use App\Livewire\Rules\Index as RuleIndex;
use App\Livewire\Rules\Show as RuleShow;
use App\Livewire\Scenarios\Index as ScenarioIndex;
use App\Livewire\Scenes\Form as SceneForm;
use App\Livewire\Scenes\Show as SceneShow;
use App\Livewire\Search\Index as SearchIndex;
use App\Livewire\Secrets\Index as SecretIndex;
use App\Livewire\Sessions\Live as SessionLive;
use App\Livewire\Sessions\Show as SessionShow;
use App\Livewire\Support\ReportBug;
use App\Livewire\Table\Remote as TableRemote;
use App\Livewire\Table\Screen as TableScreen;
use App\Livewire\Tags\Manage as TagsManage;
use App\Livewire\Timeline\Index as TimelineIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'campaigns.index' : 'login'));

// Ouvert sans être connecté : le visiteur voit l'invitation avant de se connecter ou de s'inscrire.
Route::get('/invitation/{token}', [InvitationController::class, 'show'])->name('invitations.show');

// Stripe annonce les paiements ici (signature vérifiée, pas de jeton CSRF).
Route::post('/stripe/webhook', [BillingController::class, 'webhook'])->name('stripe.webhook');

Route::middleware('auth')->group(function () {
    Route::post('/abonnement/portail', [BillingController::class, 'portal'])->name('billing.portal');
    Route::get('/abonnement/merci', [BillingController::class, 'success'])->name('billing.success');
    Route::post('/abonnement/{interval}', [BillingController::class, 'checkout'])->whereIn('interval', ['monthly', 'yearly'])->name('billing.checkout');
    Route::post('/invitation/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::livewire('/campagnes', CampaignIndex::class)->name('campaigns.index');
    Route::livewire('/preferences', Preferences::class)->name('preferences');
    Route::view('/quoi-de-neuf', 'pages.changelog')->name('changelog');
    Route::view('/configuration-recommandee', 'pages.recommended')->name('recommended');
    Route::view('/aide', 'pages.help')->name('help');
    Route::livewire('/signaler-un-probleme', ReportBug::class)->name('bugs.create');
    Route::livewire('/admin', AdminUsers::class)->name('admin.users');
    Route::livewire('/admin/formules', AdminPlans::class)->name('admin.plans');
    Route::livewire('/admin/backlog', AdminBacklog::class)->name('admin.backlog');
    Route::livewire('/admin/evolutions', AdminEvolutions::class)->name('admin.evolutions');
    Route::livewire('/admin/recettes', AdminRecettes::class)->name('admin.recettes');
    Route::redirect('/problemes-signales', '/admin/backlog')->name('bugs.index');
    Route::post('/push/abonnement', [PushSubscriptionController::class, 'store'])->name('push.store');
    Route::delete('/push/abonnement', [PushSubscriptionController::class, 'destroy'])->name('push.destroy');
    Route::livewire('/notifications', NotificationIndex::class)->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationController::class, 'open'])->name('notifications.open')->whereUuid('notification');
    Route::livewire('/types-de-fiche', EntityTypesManage::class)->name('entity-types.index');
    Route::livewire('/tags', TagsManage::class)->name('tags.index');
    Route::livewire('/campagnes/{campaign}', CampaignShow::class)->name('campaigns.show')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/entites/nouvelle', EntityForm::class)->name('entities.create')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/entites/{entity}', EntityShow::class)->name('entities.show')->whereNumber(['campaign', 'entity']);
    Route::livewire('/campagnes/{campaign}/entites/{entity}/modifier', EntityForm::class)->name('entities.edit')->whereNumber(['campaign', 'entity']);
    Route::livewire('/campagnes/{campaign}/scenarios', ScenarioIndex::class)->name('scenarios.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/scenes/nouvelle', SceneForm::class)->name('scenes.create')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/scenes/{scene}', SceneShow::class)->name('scenes.show')->whereNumber(['campaign', 'scene']);
    Route::livewire('/campagnes/{campaign}/scenes/{scene}/modifier', SceneForm::class)->name('scenes.edit')->whereNumber(['campaign', 'scene']);

    Route::livewire('/campagnes/{campaign}/session', SessionLive::class)->name('sessions.live')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/ecran-de-table', TableScreen::class)->name('table.screen')->middleware('feature:table')->whereNumber('campaign');
    Route::get('/campagnes/{campaign}/ecran-de-table/fichier', [TableScreenController::class, 'file'])->name('table.file')->middleware('feature:table')->whereNumber('campaign');
    Route::get('/campagnes/{campaign}/ecran-de-table/image', [TableScreenController::class, 'image'])->name('table.image')->middleware('feature:table')->whereNumber('campaign');
    Route::get('/campagnes/{campaign}/ecran-de-table/jetons/{token}', [TableScreenController::class, 'token'])->name('table.token')->middleware('feature:table')->whereNumber(['campaign', 'token']);
    Route::livewire('/campagnes/{campaign}/telecommande', TableRemote::class)->name('table.remote')->middleware('feature:table')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/cartes', MapIndex::class)->name('maps.index')->middleware('feature:maps')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/cartes/{map}', MapShow::class)->name('maps.show')->middleware('feature:maps')->whereNumber(['campaign', 'map']);
    Route::livewire('/campagnes/{campaign}/chronologie', TimelineIndex::class)->name('timeline.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/graphe', GraphIndex::class)->name('graph.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/assistant-ia', AiIndex::class)->name('ai.index')->middleware('feature:ai')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/sessions/{playSession}', SessionShow::class)->name('sessions.show')->whereNumber(['campaign', 'playSession']);

    Route::livewire('/campagnes/{campaign}/regles', RuleIndex::class)->name('rules.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/regles/nouvelle', RuleForm::class)->name('rules.create')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/regles/{rule}', RuleShow::class)->name('rules.show')->whereNumber(['campaign', 'rule']);
    Route::livewire('/campagnes/{campaign}/regles/{rule}/modifier', RuleForm::class)->name('rules.edit')->whereNumber(['campaign', 'rule']);
    Route::livewire('/campagnes/{campaign}/documents', DocumentIndex::class)->name('documents.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/documents/{document}', DocumentShow::class)->name('documents.show')->whereNumber(['campaign', 'document']);

    Route::livewire('/campagnes/{campaign}/recherche', SearchIndex::class)->name('search.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/personnages', CharacterIndex::class)->name('characters.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/personnages/{character}', CharacterShow::class)->name('characters.show')->middleware('offline')->whereNumber(['campaign', 'character']);
    Route::get('/campagnes/{campaign}/personnages/{character}/feuille.pdf', [FileController::class, 'characterSheet'])->name('characters.sheet')->middleware('offline')->whereNumber(['campaign', 'character']);
    Route::get('/campagnes/{campaign}/personnages/{character}/portrait', [FileController::class, 'characterPortrait'])->name('characters.portrait')->middleware('offline')->whereNumber(['campaign', 'character']);
    Route::get('/campagnes/{campaign}/personnages/{character}/fiches/{entity}', [CharacterKnowledgeController::class, 'entity'])->name('characters.entity')->middleware('offline')->whereNumber(['campaign', 'character', 'entity']);
    Route::get('/campagnes/{campaign}/personnages/{character}/fiches/{entity}/image', [CharacterKnowledgeController::class, 'entityImage'])->name('characters.entity-image')->middleware('offline')->whereNumber(['campaign', 'character', 'entity']);
    Route::get('/campagnes/{campaign}/personnages/{character}/documents/{document}', [CharacterKnowledgeController::class, 'document'])->name('characters.document')->middleware('offline')->whereNumber(['campaign', 'character', 'document']);
    Route::livewire('/campagnes/{campaign}/joueurs', MemberIndex::class)->name('members.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/messages', MessageIndex::class)->name('messages.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/journal', JournalIndex::class)->name('journal.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/secrets', SecretIndex::class)->name('secrets.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/revelations', RevealIndex::class)->name('reveals.index')->whereNumber('campaign');

    Route::livewire('/campagnes/{campaign}/champs', FieldsManage::class)->name('fields.index')->whereNumber('campaign');
    Route::livewire('/campagnes/{campaign}/import', ImportCreate::class)->name('imports.create')->whereNumber('campaign');
    Route::get('/campagnes/{campaign}/import/exemple-{kind}.csv', ImportExampleController::class)->name('imports.example')->whereNumber('campaign')->whereIn('kind', ['fiches', 'champs', 'regles', 'scenes']);
    Route::get('/campagnes/{campaign}/archive.zip', [ArchiveController::class, 'campaign'])->name('archives.campaign')->middleware('feature:archive')->whereNumber('campaign');
    Route::get('/campagnes/{campaign}/modele.json', [ArchiveController::class, 'template'])->name('archives.template')->middleware('feature:archive')->whereNumber('campaign');
    Route::get('/campagnes/{campaign}/export/{kind}.csv', ExportController::class)->name('exports.download')->whereNumber('campaign')->whereIn('kind', ['fiches', 'champs', 'regles', 'scenes']);

    Route::get('/fichiers/{attachment}', [FileController::class, 'attachment'])->name('attachments.show')->whereNumber('attachment');
    Route::get('/documents/{document}/fichier', [FileController::class, 'document'])->name('documents.file')->whereNumber('document');
    Route::get('/entites/{entity}/image', [FileController::class, 'entityImage'])->name('entities.image')->whereNumber('entity');
});
