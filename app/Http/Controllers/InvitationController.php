<?php

namespace App\Http\Controllers;

use App\Enums\CampaignRole;
use App\Models\ActivityLog;
use App\Models\CampaignInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Page ouverte par le lien d'invitation. Un visiteur non connecté voit la campagne
 * à rejoindre, se connecte ou crée son compte, puis revient ici pour accepter.
 */
class InvitationController extends Controller
{
    public function show(Request $request, string $token): View
    {
        $invitation = CampaignInvitation::with(['campaign', 'inviter'])->where('token', $token)->first();

        if ($invitation === null) {
            return view('invitations.show', ['invitation' => null, 'problem' => 'Ce lien d\'invitation n\'existe pas ou a été annulé par le MJ.']);
        }

        $member = $request->user() ? $invitation->campaign->roleOf($request->user()) : null;

        if (! $invitation->isUsable() && $member === null) {
            return view('invitations.show', [
                'invitation' => null,
                'problem' => $invitation->accepted_at
                    ? 'Ce lien d\'invitation a déjà été utilisé. Demandez-en un nouveau à votre MJ.'
                    : 'Ce lien d\'invitation a expiré. Demandez-en un nouveau à votre MJ.',
            ]);
        }

        if ($request->user() === null) {
            // Après connexion ou inscription, Fortify renvoie ici.
            redirect()->setIntendedUrl($request->url());
            $request->session()->put('invitation_campaign', $invitation->campaign->name);
        }

        return view('invitations.show', ['invitation' => $invitation, 'member' => $member, 'problem' => null]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $user = $request->user();

        $campaign = DB::transaction(function () use ($token, $user) {
            $invitation = CampaignInvitation::where('token', $token)->lockForUpdate()->first();
            abort_if($invitation === null || ! $invitation->isUsable(), 410);

            $campaign = $invitation->campaign;

            // Déjà membre : on ne touche pas au rôle, l'invitation reste disponible pour quelqu'un d'autre.
            if ($campaign->roleOf($user) !== null) {
                return $campaign;
            }

            $campaign->members()->attach($user, ['role' => $invitation->role->value]);
            $invitation->forceFill(['accepted_by' => $user->id, 'accepted_at' => now()])->save();

            ActivityLog::record('member', $user->id, $user->name, 'created', ['role' => ['old' => null, 'new' => $invitation->role->value]], ['campaign_id' => $campaign->id]);

            return $campaign;
        });

        $request->session()->forget('invitation_campaign');

        if ($campaign->roleOf($user) === CampaignRole::GameMaster) {
            return redirect()->route('campaigns.show', $campaign);
        }

        return redirect()->route('campaigns.index')->with('status', 'Vous avez rejoint la campagne « '.$campaign->name.' ».');
    }
}
