<?php

namespace App\Http\Controllers;

use App\Actions\Characters\OfflineSync;
use App\Models\Campaign;
use App\Models\PlayerCharacter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Reçoit ce que le joueur a changé sur sa fiche hors ligne (voir OfflineSync et offline-sheet.js).
 */
class CharacterOfflineSyncController extends Controller
{
    public function __invoke(Request $request, Campaign $campaign, PlayerCharacter $character, OfflineSync $sync): JsonResponse
    {
        abort_unless($character->campaign_id === $campaign->id, 404);
        // Hors de la campagne : la même réponse qu'une adresse inexistante.
        Gate::authorize('view', $character);
        // Seul le joueur du personnage : le MJ ne modifie pas la fiche hors ligne.
        abort_unless($character->isPlayedBy($request->user()), 403);

        $changes = $request->validate([
            'sync_id' => ['required', 'string', 'max:64'],
            'counters' => ['array', 'max:200'],
            'counters.*' => ['integer', 'between:-100000,100000'],
            'fields' => ['array', 'max:200'],
            'fields.*' => ['array'],
            'fields.*.base' => ['nullable'],
            'fields.*.value' => ['nullable'],
            'notes' => ['array', 'max:100'],
            'notes.*.body' => ['required', 'string', 'max:20000'],
            'notes.*.visibility' => ['required', 'string'],
            'notes.*.shares' => ['array'],
            'notes.*.shares.*' => ['integer'],
            'notes.*.written_at' => ['nullable', 'string', 'max:40'],
        ]);

        $result = $sync->handle($character, $request->user(), $changes);

        // La même synchronisation est déjà en cours (deux onglets) : l'appareil réessaiera.
        return $result === null ? response()->json(['pending' => true], 409) : response()->json($result);
    }
}
