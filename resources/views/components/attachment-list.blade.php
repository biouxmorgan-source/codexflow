@props(['attachments'])
{{-- Images en vignettes, autres fichiers en liste. --}}
@php($images = $attachments->filter->isImage())
@php($files = $attachments->reject->isImage())

@if ($images->isNotEmpty())
    <ul class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
        @foreach ($images as $attachment)
            <li wire:key="attachment-{{ $attachment->id }}" class="group relative">
                <a href="{{ route('attachments.show', $attachment) }}" target="_blank" rel="noopener">
                    <img src="{{ route('attachments.show', $attachment) }}" alt="{{ $attachment->original_name }}" loading="lazy" class="aspect-square w-full rounded-lg border border-stone-200 object-cover">
                </a>
                <button type="button" wire:click="deleteAttachment({{ $attachment->id }})" wire:confirm="Supprimer {{ $attachment->original_name }} ?" class="absolute top-1 right-1 rounded bg-white/90 px-2 py-0.5 text-xs text-red-700 shadow-sm hover:bg-white" aria-label="Supprimer {{ $attachment->original_name }}">Supprimer</button>
            </li>
        @endforeach
    </ul>
@endif

@if ($files->isNotEmpty())
    <ul class="mt-4 divide-y divide-stone-100 rounded-lg border border-stone-200">
        @foreach ($files as $attachment)
            <li wire:key="attachment-{{ $attachment->id }}" class="flex items-center gap-3 px-3 py-2 text-sm">
                <span class="shrink-0 rounded bg-stone-100 px-1.5 py-0.5 text-xs font-medium uppercase text-stone-600">{{ pathinfo($attachment->original_name, PATHINFO_EXTENSION) ?: 'fichier' }}</span>
                <a href="{{ route('attachments.show', $attachment) }}" target="_blank" rel="noopener" class="min-w-0 flex-1 truncate font-medium link">{{ $attachment->original_name }}</a>
                <span class="shrink-0 text-stone-500">{{ $attachment->humanSize() }}</span>
                <button type="button" wire:click="deleteAttachment({{ $attachment->id }})" wire:confirm="Supprimer {{ $attachment->original_name }} ?" class="shrink-0 text-red-700 hover:underline" aria-label="Supprimer {{ $attachment->original_name }}">Supprimer</button>
            </li>
        @endforeach
    </ul>
@endif
