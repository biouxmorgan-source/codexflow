<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\EmailChanged;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Lien reçu à la nouvelle adresse (signé, avec l'adresse dedans) : l'adresse du compte change
 * seulement maintenant, et l'ancienne en est prévenue.
 */
class ConfirmEmailChangeController extends Controller
{
    public function __invoke(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->is($user), 403);

        $email = (string) $request->query('email');
        $old = $user->email;

        if ($email === $old) {
            return redirect()->route('preferences')->with('status', __('Cette adresse est déjà celle de votre compte.'));
        }

        if (User::where('email', $email)->whereKeyNot($user->id)->exists()) {
            return redirect()->route('preferences')->with('status', __('Cette adresse est désormais utilisée par un autre compte : rien n’a changé.'));
        }

        $user->forceFill(['email' => $email])->save();
        DB::table('password_reset_tokens')->where('email', $old)->delete();
        Notification::route('mail', $old)->notify(new EmailChanged($email));

        return redirect()->route('preferences')->with('status', __('Votre adresse e-mail est maintenant :email. L’ancienne adresse a été prévenue.', ['email' => $email]));
    }
}
