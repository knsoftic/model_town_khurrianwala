<?php

namespace App\Support;

final class SeoData
{
    /**
     * @param  array<int, string>  $schemas  JSON-LD documents (already JSON-encoded)
     */
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $keywords = null,
        public ?string $canonical = null,
        public ?string $ogTitle = null,
        public ?string $ogDescription = null,
        public ?string $ogImage = null,
        public string $ogType = 'website',
        public ?string $robots = null,
        public array $schemas = [],
    ) {}
}
