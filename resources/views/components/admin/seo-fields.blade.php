@props(['seo' => null])

<x-admin.card title="SEO" description="Leave blank to use the page heading and the website defaults from Settings → SEO." x-data="{ open: {{ $seo?->exists || $errors->has('seo.*') ? 'true' : 'false' }} }">
    <x-slot:actions>
        <button type="button" class="a-btn-ghost a-btn-sm" @click="open = !open"><span x-text="open ? 'Hide' : 'Edit SEO'"></span></button>
    </x-slot:actions>
    <div x-show="open" x-collapse class="grid gap-4 sm:grid-cols-2">
        <x-admin.input name="seo[title]" label="SEO title" :value="$seo?->title" maxlength="255" class="sm:col-span-2" />
        <x-admin.textarea name="seo[description]" label="Meta description" :value="$seo?->description" rows="2" maxlength="500" class="sm:col-span-2" />
        <x-admin.keywords name="seo[keywords]" label="Meta keywords" :value="$seo?->keywords" class="sm:col-span-2" help="Type a keyword and press Add (or Enter). Leave empty to use the website keywords from Settings → SEO." />
        <x-admin.input name="seo[canonical_url]" label="Canonical URL" :value="$seo?->canonical_url" type="url" placeholder="https://" />
        <x-admin.select name="seo[robots]" label="Robots" :value="$seo?->robots" :options="['' => 'Default (index, follow)', 'noindex, follow' => 'noindex, follow', 'noindex, nofollow' => 'noindex, nofollow']" />
        <x-admin.input name="seo[og_title]" label="Open Graph title" :value="$seo?->og_title" />
        <x-admin.input name="seo[og_description]" label="Open Graph description" :value="$seo?->og_description" />
        <x-admin.image name="seo_og_image" label="Open Graph image" :current="$seo?->og_image" help="1200×630 recommended." class="sm:col-span-2" />
        <x-admin.textarea name="seo[schema_json]" label="Custom schema (JSON-LD)" :value="$seo?->schema_json" rows="4" :mono="true" class="sm:col-span-2" help='Optional. A JSON object, e.g. {"@context":"https://schema.org","@type":"Place",...}' />
    </div>
</x-admin.card>
