<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Sign in') · {{ setting('site_name') }}</title>
    @if (setting('favicon'))
        <link rel="icon" href="{{ media_url(setting('favicon')) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="h-full bg-white">
    <div class="grid min-h-full lg:grid-cols-2">
        <div class="relative hidden overflow-hidden bg-navy-900 text-white lg:block">
            @if ($banner = media_url(\App\Models\Page::query()->where('slug', 'plots')->value('banner_image')))
                <img src="{{ $banner }}" alt="" class="absolute inset-0 size-full object-cover opacity-60">
            @endif
            <div class="absolute inset-0 bg-gradient-to-br from-navy-900/90 via-navy-900/70 to-navy-900/30"></div>
            <div class="relative flex h-full flex-col justify-between p-12">
                <div class="flex items-center gap-3">
                    <span class="grid size-11 place-items-center rounded-xl bg-gold-500 text-navy-900"><x-icon name="land-plot" class="size-6" stroke="2" /></span>
                    <span class="text-lg font-semibold">{{ setting('site_name') }}</span>
                </div>
                <div class="max-w-md">
                    <p class="text-xs font-semibold tracking-[0.25em] text-gold-400 uppercase">Admin Panel</p>
                    <h1 class="mt-3 text-4xl leading-tight font-bold">Manage plots, plans, leads and the whole website from one place.</h1>
                    <p class="mt-4 text-slate-300">Prices, installment schedules, content, branding and bookings — all under your control.</p>
                </div>
                <p class="text-xs text-slate-400">Authorised staff only. All sign-ins are logged.</p>
            </div>
        </div>

        <div class="flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-sm">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <span class="grid size-10 place-items-center rounded-xl bg-navy-900 text-gold-400"><x-icon name="land-plot" class="size-5" stroke="2" /></span>
                    <span class="font-semibold text-slate-900">{{ setting('site_name') }}</span>
                </div>
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
