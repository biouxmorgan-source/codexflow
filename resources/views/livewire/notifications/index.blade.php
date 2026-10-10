<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('Notifications') }}</h1>
            <p class="mt-1 text-sm text-stone-600">{{ __('Ce que vous avez reçu dans vos campagnes : éléments révélés ou donnés, messages, demandes des joueurs.') }}</p>
        </div>
        <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" wire:model.live="unreadOnly"> {{ __('Non lues seulement') }}
            </label>
            <button type="button" wire:click="markAllRead" class="btn-secondary">{{ __('Tout marquer comme lu') }}</button>
        </div>
    </div>

    @if ($this->notifications->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
            <p class="text-stone-600">{{ $unreadOnly ? __('Aucune notification non lue.') : __("Aucune notification pour l'instant.") }}</p>
        </div>
    @else
        <ul class="divide-y divide-stone-200 overflow-hidden rounded-xl border border-stone-200 bg-white">
            @foreach ($this->notifications as $notification)
                @php($data = $notification->data)
                <li wire:key="notification-{{ $notification->id }}">
                    <a href="{{ route('notifications.open', $notification->id) }}" @class(['flex items-start gap-3 px-4 py-3 hover:bg-codex-soft', 'bg-flow/5' => $notification->read_at === null])>
                        <span @class(['mt-1.5 h-2 w-2 shrink-0 rounded-full', 'bg-flow' => $notification->read_at === null, 'bg-transparent' => $notification->read_at !== null]) aria-hidden="true"></span>
                        <span class="min-w-0 flex-1">
                            <span @class(['block text-sm', 'font-medium' => $notification->read_at === null])>{{ $data['text'] ?? '' }}</span>
                            <span class="block text-xs text-stone-500">
                                {{ \App\Notifications\CampaignEvent::kinds()[$data['kind'] ?? ''] ?? '' }}
                                · {{ $data['campaign'] ?? '' }}
                                · <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->translatedFormat('j M, H:i') }}</time>
                                @if ($notification->read_at === null) <span class="sr-only">{{ __('(non lue)') }}</span> @endif
                            </span>
                        </span>
                        <span class="shrink-0 text-codex" aria-hidden="true">›</span>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $this->notifications->links() }}</div>
    @endif
    <section x-data="deviceSettings" wire:ignore class="mt-8 rounded-xl border border-stone-200 bg-white p-6 shadow-sm" aria-labelledby="device-title">
        <h2 id="device-title" class="font-semibold">{{ __('Sur cet appareil') }}</h2>
        <p class="mt-1 text-sm text-stone-600">{{ __('Installez SagaWyn comme une application sur votre téléphone ou votre ordinateur : la fiche de votre personnage reste lisible sans connexion.') }}</p>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <template x-if="installed">
                <p class="text-sm text-green-800">{{ __('SagaWyn est installé sur cet appareil.') }}</p>
            </template>
            <template x-if="! installed && installable">
                <button type="button" x-on:click="install" class="btn-secondary">{{ __("Installer l'application") }}</button>
            </template>
            <template x-if="! installed && ! installable">
                <p class="text-sm text-stone-600">{{ __("Pour l'installer : menu du navigateur › « Installer l'application » ou, sur iPhone, Partager › « Sur l'écran d'accueil ».") }}</p>
            </template>
        </div>

        <div class="mt-4 border-t border-stone-100 pt-4">
            <template x-if="! secure">
                <p class="text-sm text-stone-600">{{ __("Les notifications sur l'appareil demandent une adresse sécurisée (https).") }}</p>
            </template>
            <template x-if="secure && ! pushSupported">
                <p class="text-sm text-stone-600">{{ __("Les notifications sur l'appareil ne sont pas disponibles ici. Sur iPhone, installez d'abord l'application.") }}</p>
            </template>
            <template x-if="pushSupported && permission === 'denied'">
                <p class="text-sm text-stone-600">{{ __('Les notifications sont bloquées pour ce site : autorisez-les dans les réglages du navigateur.') }}</p>
            </template>
            <template x-if="pushSupported && permission !== 'denied'">
                <div class="flex flex-wrap items-center gap-3">
                    <p class="text-sm" x-text="subscribed ? @js(__('Vous recevez vos notifications sur cet appareil, même quand SagaWyn est fermé.')) : @js(__('Recevez messages et révélations sur cet appareil, même quand SagaWyn est fermé.'))"></p>
                    <button type="button" x-show="! subscribed" x-on:click="enablePush" x-bind:disabled="busy" class="btn-primary">{{ __('Activer les notifications') }}</button>
                    <button type="button" x-show="subscribed" x-on:click="disablePush" x-bind:disabled="busy" class="btn-secondary">{{ __('Désactiver') }}</button>
                </div>
            </template>
            <p x-show="error" x-text="error" class="error mt-2" role="alert"></p>
        </div>
    </section>
</div>
