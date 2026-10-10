<?php

use App\Http\Middleware\AvailableOffline;
use App\Http\Middleware\EnsureFeature;
use App\Http\Middleware\RecordResponseTime;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ThrottleAccountForms;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['offline' => AvailableOffline::class, 'feature' => EnsureFeature::class]);
        // AuthenticateSession : changer son mot de passe déconnecte ses autres appareils.
        $middleware->web(append: [SetLocale::class, AuthenticateSession::class, ThrottleAccountForms::class]);
        // La langue est fixée avant la liaison des modèles : une 404 d'élément introuvable est traduite.
        $middleware->prependToPriorityList(SubstituteBindings::class, SetLocale::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->append(RecordResponseTime::class);
        $middleware->validateCsrfTokens(except: ['stripe/webhook']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
