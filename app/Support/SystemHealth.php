<?php

namespace App\Support;

use App\Notifications\Channels\PushChannel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Ce que l'administrateur doit savoir de l'installation, en tête de la console :
 * l'envoi push est-il possible, a-t-il échoué récemment ?
 */
class SystemHealth
{
    private const PUSH_FAILURE = 'loremundi:push-failure';

    /** @return list<string> avertissements, vide quand tout va bien */
    public static function warnings(): array
    {
        $warnings = [];

        if (! PushChannel::enabled()) {
            $warnings[] = __('Clés VAPID absentes : les notifications push sont désactivées (php artisan webpush:vapid).');
        } elseif (! self::hasBigMath()) {
            $warnings[] = __('Ni l’extension PHP GMP ni BCMath n’est installée : les notifications push ne peuvent pas partir. Installez l’une des deux sur le serveur.');
        }

        $failure = Cache::get(self::PUSH_FAILURE);
        if (is_array($failure) && isset($failure['at'])) {
            $warnings[] = trans_choice(
                'Envoi push en échec : :count fois depuis le :date (dernier message : « :message »).|Envoi push en échec : :count fois depuis le :date (dernier message : « :message »).',
                (int) ($failure['count'] ?? 1),
                ['date' => Carbon::parse($failure['first'] ?? $failure['at'])->isoFormat('LLL'), 'message' => mb_strimwidth((string) ($failure['message'] ?? ''), 0, 160, '…')],
            );
        }

        return $warnings;
    }

    public static function hasBigMath(): bool
    {
        return extension_loaded('gmp') || extension_loaded('bcmath');
    }

    /** Un envoi push a échoué : retenu sept jours pour la console. */
    public static function pushFailed(Throwable $error): void
    {
        $failure = Cache::get(self::PUSH_FAILURE);
        $failure = is_array($failure) ? $failure : ['first' => now()->toIso8601String(), 'count' => 0];

        Cache::put(self::PUSH_FAILURE, [
            'first' => $failure['first'],
            'count' => (int) $failure['count'] + 1,
            'at' => now()->toIso8601String(),
            'message' => $error->getMessage(),
        ], now()->addDays(7));
    }
}
