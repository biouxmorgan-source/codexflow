<?php

namespace App\Http\Controllers;

use App\Models\BugReport;
use App\Models\CharacterNote;
use App\Models\Message;
use App\Models\PlayerCharacter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * « Mes données » (RGPD) : tout ce que LoreMundi garde sur la personne connectée, en JSON.
 * Le contenu complet d'une campagne se télécharge depuis la campagne (sauvegarde complète).
 */
class AccountDataController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $data = [
            'exported_at' => now()->toIso8601String(),
            'account' => [
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at?->toIso8601String(),
                'preferences' => $user->preferences ?? [],
                'plan' => $user->plan,
                'trial_started_at' => $user->trial_started_at?->toIso8601String(),
                'two_factor_enabled' => $user->two_factor_confirmed_at !== null,
                'ai_provider' => $user->ai_provider,
                'ai_model' => $user->ai_model,
                'ai_api_key_saved' => filled($user->ai_api_key),
            ],
            'logins' => $user->logins()->orderBy('logged_in_at')->pluck('logged_in_at')->map->toIso8601String()->all(),
            'campaigns' => $user->campaigns()->orderBy('name')->get()->map(fn ($campaign) => [
                'name' => $campaign->name,
                'role' => $campaign->pivot->role->value,
                'owner' => $campaign->user_id === $user->id,
            ])->all(),
            'worlds' => $user->worlds()->orderBy('name')->pluck('name')->all(),
            'characters' => PlayerCharacter::with(['entity', 'campaign'])->where('user_id', $user->id)->get()->map(fn ($character) => [
                'name' => $character->entity?->name,
                'campaign' => $character->campaign?->name,
            ])->all(),
            'notes' => CharacterNote::with('character.campaign')->where('user_id', $user->id)->orderBy('created_at')->get()->map(fn ($note) => [
                'campaign' => $note->character?->campaign?->name,
                'visibility' => $note->visibility,
                'body' => $note->body,
                'created_at' => $note->created_at?->toIso8601String(),
            ])->all(),
            'messages' => Message::with('campaign')->where('sender_id', $user->id)->orderBy('created_at')->get()->map(fn ($message) => [
                'campaign' => $message->campaign?->name,
                'body' => $message->body,
                'created_at' => $message->created_at?->toIso8601String(),
            ])->all(),
            'bug_reports' => BugReport::where('user_id', $user->id)->orderBy('created_at')->get()->map(fn ($report) => [
                'message' => $report->message,
                'url' => $report->url,
                'created_at' => $report->created_at?->toIso8601String(),
            ])->all(),
        ];

        return response(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="loremundi-mes-donnees.json"',
        ]);
    }
}
