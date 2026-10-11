@props(['text'])
{{-- Version officielle remplacée par un contenu maison : zone MJ, repliée pour ne pas encombrer la fiche. --}}
<details class="mt-4 rounded-lg border border-amber-200 bg-amber-50/60 p-3">
    <summary class="cursor-pointer text-sm font-medium text-amber-900">{{ __('Version officielle remplacée') }}</summary>
    <div class="mt-2 text-sm text-stone-700">{{ $text }}</div>
</details>
