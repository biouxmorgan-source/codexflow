@php($link = $this->link($entry))
@php($lines = $entry->lines($this->definitions))
<li class="text-sm">
    <div class="flex flex-wrap items-baseline gap-x-2">
        @unless ($compact)
            <span class="text-stone-500">{{ $entry->created_at->isoFormat('D MMM YYYY, HH:mm') }}</span>
            <span class="font-medium">{{ $entry->user?->name ?? __('Système') }}</span>
        @endunless
        <span @class(['text-green-800' => $entry->event === 'created', 'text-red-700' => $entry->event === 'deleted'])>{{ $compact ? $entry->compactVerb() : $entry->verb() }}</span>
        <span class="text-stone-500">{{ mb_strtolower($entry->subjectName()) }}</span>
        @if ($link)
            <a href="{{ $link }}" class="link" wire:navigate>{{ $entry->subject_label }}</a>
        @else
            <span class="font-medium">{{ $entry->subject_label }}</span>
        @endif
        @if ($subject === '' && in_array($entry->subject_type, ['entity', 'scene', 'rule', 'document'], true))
            <button type="button" wire:click="$set('subject', '{{ $entry->subject_type }}:{{ $entry->subject_id }}')" class="text-xs text-stone-500 underline decoration-stone-300 underline-offset-2 hover:text-codex">{{ __('historique') }}</button>
        @endif
    </div>
    @if ($lines !== [] && ($entry->event === 'updated' || ! $compact))
        @if ($entry->event !== 'updated')
            <details class="mt-1">
                <summary class="text-xs text-stone-500 underline decoration-stone-300 underline-offset-2 hover:text-codex">{{ $entry->event === 'deleted' ? __('Contenu au moment de la suppression') : __('Contenu à la création') }}</summary>
        @endif
        <dl class="mt-2 space-y-1 border-l-2 border-stone-200 pl-3">
            @foreach ($lines as [$label, $old, $new])
                <div class="grid gap-x-3 sm:grid-cols-[10rem_1fr]">
                    <dt class="text-stone-500">{{ $label }}</dt>
                    <dd class="min-w-0 break-words">
                        @if ($entry->event === 'updated')
                            <span class="text-red-700 line-through decoration-red-300">{{ \Illuminate\Support\Str::limit($old, 300) ?: __('(vide)') }}</span>
                            <span class="text-stone-400" aria-label="{{ __('devient') }}">→</span>
                            <span class="text-green-800">{{ \Illuminate\Support\Str::limit($new, 300) ?: __('(vide)') }}</span>
                        @else
                            {{ \Illuminate\Support\Str::limit($entry->event === 'deleted' ? $old : $new, 300) }}
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
        @if ($entry->event !== 'updated')
            </details>
        @endif
    @endif
</li>
