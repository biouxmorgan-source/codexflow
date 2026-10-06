<?php

namespace App\Support\Search;

final readonly class SearchResult
{
    /**
     * @param  array{label: string, text: string}|null  $snippet
     */
    public function __construct(
        public string $title,
        public string $subtitle,
        public string $url,
        public ?array $snippet = null,
    ) {}
}
