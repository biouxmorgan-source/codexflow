@props(['user'])
{{-- Contributeur du Kickstarter. --}}
@if ($user?->isFounder())
    <span {{ $attributes->class('inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 align-middle text-xs font-medium text-amber-900') }} title="{{ __('Fondateur depuis le :date', ['date' => $user->founder_since->isoFormat('L')]) }}">★ {{ __('Fondateur') }}</span>
@endif
