<div class="max-w-4xl">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('Problèmes signalés') }}</h1>
            <p class="mt-1 text-sm text-stone-600">{{ trans_choice(':count signalement à traiter|:count signalements à traiter', $openCount) }}</p>
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model.live="showResolved"> {{ __('Afficher aussi les problèmes réglés') }}
        </label>
    </div>

    <ul class="space-y-3">
        @forelse ($this->reports as $report)
            <li wire:key="report-{{ $report->id }}" @class(['rounded-xl border border-stone-200 bg-white p-4 shadow-sm', 'opacity-60' => $report->resolved_at])>
                <div class="mb-2 flex flex-wrap items-center justify-between gap-2 text-sm text-stone-500">
                    <span>
                        <span class="font-medium text-ink">{{ $report->user?->name ?? __('Compte supprimé') }}</span>
                        @if ($report->user) · {{ $report->user->email }} @endif
                        · {{ $report->created_at->isoFormat('L LT') }}
                    </span>
                    <span class="flex gap-2">
                        <button type="button" wire:click="toggleResolved({{ $report->id }})" class="btn-secondary py-1 text-sm">{{ $report->resolved_at ? __('Rouvrir') : __('Marquer comme réglé') }}</button>
                        <button type="button" wire:click="delete({{ $report->id }})" wire:confirm="{{ __('Supprimer ce signalement ?') }}" class="btn-secondary py-1 text-sm text-red-700">{{ __('Supprimer') }}</button>
                    </span>
                </div>
                <p class="whitespace-pre-line text-stone-800">{{ $report->message }}</p>
                <p class="mt-2 text-xs break-all text-stone-500">
                    @if ($report->url) {{ $report->url }} · @endif
                    {{ $report->locale }} · CodexFlow {{ $report->version }} · {{ $report->user_agent }}
                </p>
            </li>
        @empty
            <li class="rounded-xl border border-dashed border-stone-300 p-6 text-center text-stone-500">{{ __('Aucun problème à traiter.') }}</li>
        @endforelse
    </ul>
</div>
