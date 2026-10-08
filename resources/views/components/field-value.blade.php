@props(['definition', 'value', 'campaign', 'character' => null])
{{--
    Valeur d'un champ libre. Vue MJ par défaut ; avec $character (fiche vue par son joueur),
    aucune fiche n'est liée et seuls les documents que le personnage connaît s'ouvrent.
--}}
@switch($definition->type)
    @case(\App\Enums\FieldType::LongText)
    @case(\App\Enums\FieldType::EntityRef)
        <span @class(['font-normal text-stone-700' => $definition->type === \App\Enums\FieldType::LongText])>{{ $character ? \App\Support\EntityLinks::plain($value, inline: true) : \App\Support\EntityLinks::inline($value, $campaign) }}</span>
        @break
    @case(\App\Enums\FieldType::Link)
        @if (is_string($value) && preg_match('#^https?://#i', $value))
            <a href="{{ $value }}" target="_blank" rel="noopener noreferrer nofollow" class="link break-all" title="{{ $value }}">{{ \Illuminate\Support\Str::limit(preg_replace('#^https?://(www\.)?#i', '', $value), 50) }}</a>
        @else
            {{ $definition->type->format($value) }}
        @endif
        @break
    @case(\App\Enums\FieldType::File)
        @php($document = is_numeric($value) ? $campaign->availableDocuments()->find((int) $value) : null)
        @if ($document === null)
            <span class="text-stone-500">{{ __('Document introuvable') }}</span>
        @elseif ($character === null)
            <a href="{{ route('documents.show', [$campaign, $document]) }}" class="link" wire:navigate>{{ $document->title }}</a>
        @elseif ($character->grants()->where('document_id', $document->id)->exists())
            <a href="{{ route('characters.document', [$campaign, $character, $document]) }}" target="_blank" class="link">{{ $document->title }}</a>
        @else
            {{ $document->title }}
        @endif
        @break
    @default
        {{ $definition->type->format($value) }}
@endswitch
