<?php

namespace App\Http\Controllers;

use App\Models\GiftCode;
use App\Support\GiftCodes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Lien de partage d'un code cadeau. Un visiteur non connecté voit ce qu'on lui offre,
 * se connecte ou crée son compte, puis revient ici pour l'utiliser.
 */
class GiftCodeController extends Controller
{
    public function show(Request $request, string $code): View
    {
        $gift = GiftCode::with('owner')->where('code', GiftCode::normalize($code))->first();

        if ($request->user()) {
            $problem = GiftCodes::problem($request->user(), $gift);
        } else {
            $problem = match (true) {
                $gift === null => __('Ce code n’existe pas. Vérifiez qu’il est bien recopié.'),
                $gift->isRedeemed() => __('Ce code a déjà été utilisé.'),
                default => null,
            };

            if ($problem === null) {
                // Après connexion ou inscription, Fortify renvoie ici.
                redirect()->setIntendedUrl($request->url());
            }
        }

        return view('gifts.show', ['gift' => $gift, 'problem' => session('problem', $problem)]);
    }

    public function redeem(Request $request, string $code): RedirectResponse
    {
        $problem = GiftCodes::redeem($request->user(), $code);

        if ($problem) {
            return redirect()->route('gifts.show', $code)->with('problem', $problem);
        }

        return redirect()->route('rewards')->with('status', __('Code utilisé : votre Premium court jusqu’au :date.', ['date' => $request->user()->fresh()->plan_ends_at->isoFormat('LL')]));
    }
}
