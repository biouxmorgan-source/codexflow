@props(['roll', 'rolling' => false, 'large' => false])
{{-- Un lancer lu : les dés (relances accrochées au dé qui les a provoquées), puis le résultat en grand. --}}
@php
    $result = $roll['result'];
    $size = $large ? 'size-16 md:size-24' : 'size-10';
    $small = $large ? 'size-10 md:size-14' : 'size-7';
    $hit = fn (int $v) => $roll['count_high'] ? $v >= $result['threshold'] : $v <= $result['threshold'];
@endphp
<div {{ $attributes->class(['flex flex-col items-center gap-3 text-center']) }}>
    <div class="flex flex-wrap items-end justify-center gap-x-3 gap-y-2">
        @foreach ($result['dice'] as $index => $group)
            <span class="inline-flex items-end gap-0.5" wire:key="die-{{ $roll['id'] }}-{{ $index }}">
                @foreach ($group['rolls'] as $step => $face)
                    @php($state = match (true) {
                        $face['sub'] => 'sub',
                        $roll['reading'] === 'count' => $hit($face['v']) ? 'hit' : 'miss',
                        ! $group['kept'] => 'dropped',
                        default => 'kept',
                    })
                    @if ($step > 0)
                        <span @class(['font-bold', $face['sub'] ? 'text-flow' : 'text-codex', $large ? 'text-xl' : 'text-xs']) aria-hidden="true">{{ $face['sub'] ? '−' : '+' }}</span>
                    @endif
                    <x-die :sides="$group['sides'] ?? $roll['sides']" :value="$face['v']" :state="$state" :size="$step === 0 ? $size : $small" :rolling="$rolling"
                        style="{{ $rolling ? 'animation-delay: '.(($index * 60) + ($step * 120)).'ms' : '' }}" />
                @endforeach
            </span>
        @endforeach
    </div>
    <div @if ($roll['reading'] === 'raw') class="sr-only" @endif>
        <p @class(['font-semibold tracking-tight', 'text-4xl md:text-6xl' => $large, 'text-2xl' => ! $large, 'text-wyn' => $result['success'] === true, 'text-flow' => $result['success'] === false])
            @if ($rolling) x-data x-init="$el.classList.add('dice-reveal')" @endif>{{ $roll['headline'] }}</p>
        @if ($result['total'] !== null && $result['success'] !== null)
            <p @class(['opacity-75', 'text-lg md:text-2xl' => $large, 'text-sm' => ! $large])>
                {{ __(':total pour un seuil de :threshold', ['total' => $result['total'], 'threshold' => $result['threshold']]) }}
            </p>
        @elseif ($result['total'] !== null && $roll['modifier'] !== 0 && $roll['reading'] !== 'below')
            <p @class(['opacity-75', 'text-lg' => $large, 'text-xs' => ! $large])>{{ __('dont :bonus de bonus', ['bonus' => ($roll['modifier'] > 0 ? '+' : '').$roll['modifier']]) }}</p>
        @endif
    </div>
</div>
