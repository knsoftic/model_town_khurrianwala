{{-- The SEO guide opened from its private link: the guide only, no admin menu. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <title>SEO Guide · {{ setting('site_name') }}</title>
    @if (setting('favicon'))
        <link rel="icon" href="{{ media_url(setting('favicon')) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    @vite(['resources/css/admin.css'])
</head>
<body>
    <header class="bg-navy-900 text-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <div class="flex min-w-0 items-center gap-3">
                @if ($logo = media_url(setting('logo')))
                    <img src="{{ $logo }}" alt="{{ setting('site_name') }}" class="h-9 w-auto max-w-[140px] object-contain">
                @else
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-gold-500 text-navy-900"><x-icon name="land-plot" class="size-5" stroke="2" /></span>
                @endif
                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold">{{ setting('site_name') }}</span>
                    <span class="block text-[11px] text-slate-400">SEO Guide</span>
                </span>
            </div>
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="inline-flex shrink-0 items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">
                <x-icon name="globe" class="size-4" /> <span class="hidden sm:inline">Website</span>
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <x-admin.page-header title="SEO Guide" description="What is already done, what still needs work, and step-by-step instructions for on-page, technical and off-page SEO." />

        <p class="mb-6 flex gap-2.5 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
            <x-icon name="lock" class="mt-0.5 size-4 text-sky-700" />
            <span>Buttons with a lock open the website’s admin panel, which asks you to sign in. Everything else opens without a login.</span>
        </p>

        @include('admin.seo-guide._guide')
    </main>

    <footer class="border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        {{ setting('site_name') }} · SEO Guide
    </footer>
</body>
</html>
