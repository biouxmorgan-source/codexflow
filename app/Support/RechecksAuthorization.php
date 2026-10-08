<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

use function Livewire\after;
use function Livewire\before;
use function Livewire\on;
use function Livewire\store;

/**
 * Les droits vérifiés à l'ouverture d'un composant Livewire le sont de nouveau à chaque
 * requête suivante : un joueur retiré ou un co-MJ rétrogradé dont la page est restée
 * ouverte ne reçoit plus rien dès son prochain échange avec le serveur.
 *
 * Les autorisations accordées pendant mount() sont notées (capacité + arguments), gardées
 * dans le mémo signé du composant, puis revérifiées à chaque hydratation.
 */
class RechecksAuthorization
{
    /** @var list<list<array{0: string, 1: array}>> une pile : un relevé par composant en cours de montage */
    private static array $frames = [];

    public static function register(): void
    {
        Gate::after(function ($user, string $ability, $result, array $arguments) {
            if ($result !== true || self::$frames === []) {
                return;
            }

            $encoded = self::encode($arguments);
            if ($encoded !== null) {
                self::$frames[array_key_last(self::$frames)][] = [$ability, $encoded];
            }
        });

        before('mount', function () {
            self::$frames[] = [];
        });

        after('mount', function ($component) {
            $checks = array_pop(self::$frames) ?? [];
            store($component)->set('rechecked-abilities', array_values(array_unique($checks, SORT_REGULAR)));
        });

        on('hydrate', function ($component, $memo) {
            $checks = $memo['abilities'] ?? [];
            store($component)->set('rechecked-abilities', $checks);

            foreach ($checks as [$ability, $arguments]) {
                Gate::authorize($ability, self::decode($arguments));
            }
        });

        on('dehydrate', function ($component, $context) {
            $checks = store($component)->get('rechecked-abilities', []);
            if ($checks !== []) {
                $context->addMemo('abilities', $checks);
            }
        });
    }

    /** @return array<int, array{0: string, 1: mixed}>|null null si un argument ne peut pas être revérifié */
    private static function encode(array $arguments): ?array
    {
        $encoded = [];
        foreach ($arguments as $argument) {
            $encoded[] = match (true) {
                $argument instanceof Model && $argument->exists => ['m', $argument::class, $argument->getKey()],
                $argument === null, is_scalar($argument) => ['v', $argument],
                default => null,
            };
            if (end($encoded) === null) {
                return null;
            }
        }

        return $encoded;
    }

    private static function decode(array $arguments): array
    {
        return array_map(fn (array $argument) => $argument[0] === 'm'
            ? $argument[1]::query()->findOrFail($argument[2])
            : $argument[1], $arguments);
    }
}
