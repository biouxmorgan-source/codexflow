<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

/**
 * Les formulaires de compte de Fortify (inscription, mot de passe oublié, réinitialisation)
 * n'ont pas de limite propre : on leur applique le limiteur « account-forms ».
 */
class ThrottleAccountForms
{
    private const PATHS = ['register', 'forgot-password', 'reset-password'];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('post') && in_array($request->path(), self::PATHS, true)) {
            return app(ThrottleRequests::class)->handle($request, $next, 'account-forms');
        }

        return $next($request);
    }
}
