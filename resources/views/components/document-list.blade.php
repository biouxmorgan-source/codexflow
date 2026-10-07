@props(['documents', 'campaign', 'unlink' => null])
{{-- Liste compacte de documents, avec retrait facultatif (méthode Livewire $unlink). --}}
@if ($documents->isEmpty())
    <p class="text-sm text-stone-500">Aucun document lié.</p>
@else
    <ul class="space-y-1">
        @foreach ($documents as $document)
            <li wire:key="document-{{ $document->id }}" class="flex items-center gap-2">
                <span class="w-9 shrink-0 rounded bg-stone-100 py-0.5 text-center text-[10px] font-semibold text-stone-500 uppercase" aria-hidden="true">{{ $document->isPdf() ? 'PDF' : 'IMG' }}</span>
                <a href="{{ route('documents.show', [$campaign, $document]) }}" class="min-w-0 flex-1 truncate text-sm link" wire:navigate>{{ $document->title }}</a>
                <a href="{{ route('documents.file', $document) }}" target="_blank" rel="noopener" class="shrink-0 rounded px-1 text-sm text-codex hover:bg-codex-soft" title="Ouvrir dans un nouvel onglet" aria-label="Ouvrir {{ $document->title }} dans un nouvel onglet">↗</a>
                @if ($unlink)
                    <button type="button" wire:click="{{ $unlink }}({{ $document->id }})" class="shrink-0 text-xs text-stone-400 hover:text-red-700" aria-label="Retirer {{ $document->title }}">✕</button>
                @endif
            </li>
        @endforeach
    </ul>
@endif
