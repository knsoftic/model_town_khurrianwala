<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@hasSection('title')@yield('title') · @endif Admin · {{ setting('site_name') }}</title>
    @if (setting('favicon'))
        <link rel="icon" href="{{ media_url(setting('favicon')) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @stack('head')
</head>
<body class="h-full" x-data="{ sidebar: false }" @keydown.escape.window="sidebar = false">
    {{-- Sidebar --}}
    <div x-cloak x-show="sidebar" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden" @click="sidebar = false"></div>
    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-navy-900 text-white transition-transform duration-200 lg:translate-x-0">
        <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
            @if ($adminLogo)
                <img src="{{ $adminLogo }}" alt="" class="h-9 w-auto max-w-[160px] object-contain">
            @else
                <span class="grid size-9 place-items-center rounded-lg bg-gold-500 text-navy-900"><x-icon name="land-plot" class="size-5" stroke="2" /></span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold">{{ setting('site_name') }}</span>
                    <span class="block text-[11px] text-slate-400">Admin Panel</span>
                </span>
            @endif
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5" aria-label="Admin">
            @foreach ($adminMenu as $group)
                <div>
                    @if ($group['label'])
                        <p class="mb-2 px-3 text-[10px] font-semibold tracking-[0.18em] text-slate-500 uppercase">{{ $group['label'] }}</p>
                    @endif
                    <ul class="space-y-0.5">
                        @foreach ($group['items'] as $item)
                            <li>
                                <a href="{{ $item['url'] }}" @class(['a-nav-link', 'is-active' => $item['active']]) @if ($item['active']) aria-current="page" @endif>
                                    <x-icon :name="$item['icon']" :class="$item['active'] ? 'size-[18px] text-gold-400' : 'size-[18px] text-slate-400'" />
                                    <span class="flex-1 truncate">{{ $item['label'] }}</span>
                                    @if ($item['badge'])
                                        <span class="rounded-full bg-gold-500 px-1.5 py-0.5 text-[10px] font-bold text-navy-900">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-3">
            <a href="{{ route('home') }}" target="_blank" class="a-nav-link"><x-icon name="external-link" class="size-[18px] text-slate-400" /> View Website</a>
        </div>
    </aside>

    <div class="flex min-h-full flex-col lg:pl-64">
        {{-- Top bar --}}
        <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
            <button type="button" class="a-icon-btn lg:hidden" @click="sidebar = true" aria-label="Open menu"><x-icon name="menu" class="size-5" /></button>

            <div class="min-w-0 flex-1">
                @hasSection('breadcrumbs')
                    <nav class="flex items-center gap-1.5 truncate text-xs text-slate-500" aria-label="Breadcrumb">@yield('breadcrumbs')</nav>
                @endif
            </div>

            <a href="{{ route('home') }}" target="_blank" class="a-btn-ghost hidden sm:inline-flex"><x-icon name="globe" class="size-4" /> Website</a>

            {{-- Notifications --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" class="a-icon-btn relative size-9" @click="open = !open" aria-label="Notifications">
                    <x-icon name="bell" class="size-5" />
                    @if ($unreadCount)
                        <span class="absolute -top-0.5 -right-0.5 grid min-w-4 place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                    @endif
                </button>
                <div x-cloak x-show="open" x-transition.origin.top.right class="absolute right-0 mt-2 w-[22rem] max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                        <p class="text-sm font-semibold text-slate-900">Notifications</p>
                        @if ($unreadCount)
                            <form method="POST" action="{{ route('admin.notifications.read-all') }}">@csrf<button class="text-xs font-semibold text-gold-700 hover:underline">Mark all read</button></form>
                        @endif
                    </div>
                    <ul class="max-h-96 divide-y divide-slate-100 overflow-y-auto">
                        @forelse ($latestNotifications as $notification)
                            <li>
                                <a href="{{ route('admin.notifications.open', $notification->id) }}" @class(['flex gap-3 px-4 py-3 transition hover:bg-slate-50', 'bg-gold-50/60' => ! $notification->read_at])>
                                    <span @class(['mt-0.5 grid size-8 shrink-0 place-items-center rounded-full', 'bg-sky-100 text-sky-700' => ($notification->data['kind'] ?? '') === 'lead', 'bg-emerald-100 text-emerald-700' => ($notification->data['kind'] ?? '') === 'booking', 'bg-violet-100 text-violet-700' => ($notification->data['kind'] ?? '') === 'contact'])>
                                        <x-icon :name="['lead' => 'user-plus', 'booking' => 'bookmark-check', 'contact' => 'mail'][$notification->data['kind'] ?? 'lead'] ?? 'bell'" class="size-4" />
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-medium text-slate-900">{{ $notification->data['title'] ?? 'Notification' }}</span>
                                        <span class="block truncate text-xs text-slate-500">{{ $notification->data['summary'] ?? '' }}</span>
                                        <span class="mt-0.5 block text-[11px] text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="px-4 py-10 text-center text-sm text-slate-500">You're all caught up.</li>
                        @endforelse
                    </ul>
                    <a href="{{ route('admin.notifications.index') }}" class="block border-t border-slate-100 py-2.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50">View all notifications</a>
                </div>
            </div>

            {{-- User menu --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="flex items-center gap-2.5 rounded-lg p-1 pr-2 transition hover:bg-slate-100">
                    @if (auth()->user()->avatar)
                        <img src="{{ media_url(auth()->user()->avatar) }}" alt="" class="size-8 rounded-full object-cover">
                    @else
                        <span class="grid size-8 place-items-center rounded-full bg-navy-900 text-xs font-semibold text-gold-400">{{ auth()->user()->initials() }}</span>
                    @endif
                    <span class="hidden text-left leading-tight md:block">
                        <span class="block text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</span>
                        <span class="block text-[11px] text-slate-500">{{ auth()->user()->role?->name }}</span>
                    </span>
                    <x-icon name="chevron-down" class="hidden size-4 text-slate-400 md:block" />
                </button>
                <div x-cloak x-show="open" x-transition.origin.top.right class="absolute right-0 mt-2 w-52 overflow-hidden rounded-xl border border-slate-200 bg-white py-1.5 shadow-xl">
                    <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"><x-icon name="user-round" class="size-4" /> My Profile</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="flex w-full items-center gap-2.5 px-4 py-2 text-left text-sm text-rose-600 hover:bg-rose-50"><x-icon name="log-out" class="size-4" /> Sign out</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            @if (session('success'))
                <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                    <x-icon name="circle-check" class="mt-0.5 size-5 text-emerald-600" /> <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-6 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                    <x-icon name="circle-alert" class="mt-0.5 size-5 text-rose-600" /> <span>{{ session('error') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                    <x-icon name="circle-alert" class="mt-0.5 size-5 text-rose-600" />
                    <div>
                        <p class="font-semibold">Please fix the following:</p>
                        <ul class="mt-1 list-disc pl-5">
                            @foreach (collect($errors->all())->unique()->take(6) as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="px-4 py-5 text-xs text-slate-400 sm:px-6 lg:px-8">
            {{ setting('site_name') }} · Admin Panel
        </footer>
    </div>

    {{-- Toasts --}}
    <div class="pointer-events-none fixed right-4 bottom-4 z-[100] flex w-80 max-w-[calc(100vw-2rem)] flex-col gap-2" aria-live="polite">
        <template x-for="toast in $store.toast.items" :key="toast.id">
            <div x-transition class="pointer-events-auto flex items-start gap-2.5 rounded-xl px-4 py-3 text-sm shadow-xl"
                 :class="toast.type === 'error' ? 'bg-rose-600 text-white' : 'bg-navy-900 text-white'">
                <x-icon name="circle-check" class="mt-0.5 size-4 text-gold-400" x-show="toast.type !== 'error'" />
                <x-icon name="circle-alert" class="mt-0.5 size-4" x-show="toast.type === 'error'" />
                <span x-text="toast.message"></span>
            </div>
        </template>
    </div>

    {{-- Confirm dialog --}}
    <div x-data x-cloak x-show="$store.confirm.open" class="fixed inset-0 z-[90] grid place-items-center p-4" role="dialog" aria-modal="true" @keydown.escape.window="$store.confirm.open = false">
        <div x-show="$store.confirm.open" x-transition.opacity class="absolute inset-0 bg-slate-900/60" @click="$store.confirm.open = false"></div>
        <div x-show="$store.confirm.open" x-transition.scale.95 class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div class="flex gap-4">
                <span class="grid size-11 shrink-0 place-items-center rounded-full bg-rose-100 text-rose-600"><x-icon name="triangle-alert" class="size-5" /></span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900" x-text="$store.confirm.title"></h2>
                    <p class="mt-1 text-sm text-slate-600" x-text="$store.confirm.message"></p>
                </div>
            </div>
            <form method="POST" :action="$store.confirm.action" class="mt-6 flex justify-end gap-2">
                @csrf
                <input type="hidden" name="_method" :value="$store.confirm.method">
                <button type="button" class="a-btn-secondary" @click="$store.confirm.open = false">Cancel</button>
                <button type="submit" class="a-btn-danger" x-text="$store.confirm.button"></button>
            </form>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
