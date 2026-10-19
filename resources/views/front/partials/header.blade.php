@php
    $sticky = setting_enabled('header_sticky', true);
    $primaryText = setting('header_cta_primary_text');
    $secondaryText = setting('header_cta_secondary_text');
    $primaryUrl = url(setting('header_cta_primary_url', '/book-plot'));
    $secondaryUrl = url(setting('header_cta_secondary_url', '/contact'));
    $phone = setting('phone');
    $email = setting('email');
@endphp

<div data-site-chrome @class(['relative z-40', 'bg-header text-header-ink' => ! $transparent, 'text-white' => $transparent])>
    @if (setting_enabled('demo_notice_enabled') && setting('demo_notice_text'))
        {{-- Fixed 28px height: the transparent-header hero offset (layouts/front) relies on it. --}}
        <div class="h-7 bg-accent text-primary">
            <p class="container-x flex h-full items-center justify-center gap-2 text-[11px] font-semibold sm:text-xs" title="{{ setting('demo_notice_text') }}">
                <x-icon name="info" class="size-3.5" stroke="2.25" />
                <span class="truncate">{{ setting('demo_notice_text') }}</span>
            </p>
        </div>
    @endif

    @if (setting_enabled('topbar_enabled'))
        <div @class(['hidden text-xs md:block', 'border-b border-white/10 bg-black/15' => ! $transparent, 'border-b border-white/15' => $transparent])>
            <div class="container-x flex h-10 items-center justify-between gap-6 opacity-85">
                <div class="flex items-center gap-5">
                    @if ($phone)
                        <a href="{{ tel_url($phone) }}" class="flex items-center gap-1.5 transition hover:text-accent">
                            <x-icon name="phone" class="size-3.5" /> {{ $phone }}
                        </a>
                    @endif
                    @if ($email)
                        <a href="mailto:{{ $email }}" class="flex items-center gap-1.5 transition hover:text-accent">
                            <x-icon name="mail" class="size-3.5" /> {{ $email }}
                        </a>
                    @endif
                    @if (setting('office_hours'))
                        <span class="hidden items-center gap-1.5 lg:flex"><x-icon name="clock" class="size-3.5" /> {{ setting('office_hours') }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    @if (setting('topbar_text'))
                        <span class="hidden font-semibold text-accent xl:inline">{{ setting('topbar_text') }}</span>
                    @endif
                    @foreach ($socialLinks as $social)
                        <a href="{{ $social->url }}" target="_blank" rel="noopener" class="transition hover:text-accent" aria-label="{{ $social->displayLabel() }}">
                            <x-icon :name="$social->iconName()" class="size-3.5" />
                        </a>
                    @endforeach
                    @if ($secondaryText)
                        <a href="{{ $secondaryUrl }}" class="ml-1 inline-flex items-center gap-1.5 rounded-full border border-current/30 px-3 py-1 font-semibold transition hover:border-accent hover:text-accent">
                            <x-icon name="headset" class="size-3.5" /> {{ $secondaryText }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<header x-data="siteHeader(@js($transparent))" data-site-header
        @class(['top-0 z-50 transition-[background-color,box-shadow] duration-300', 'sticky' => $sticky, 'relative' => ! $sticky, 'bg-header text-header-ink' => ! $transparent, 'text-white' => $transparent])
        :class="solid ? 'bg-header! text-header-ink! shadow-[0_10px_30px_-14px_rgb(0_0_0/0.5)]' : 'bg-transparent'">
    <div class="container-x flex h-[76px] max-w-[1440px] items-center justify-between gap-5">
        <a href="{{ route('home') }}" class="shrink-0" aria-label="{{ setting('site_name') }} — Home">
            <x-front.logo :url="$logoUrl" />
        </a>

        <nav class="hidden items-center gap-0.5 xl:flex" aria-label="Main">
            @foreach ($headerMenu as $item)
                @if ($item->children->isNotEmpty())
                    <div x-data="{ open: false }" class="relative" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button" @click="open = !open" class="flex items-center gap-1 rounded-full px-2.5 py-2 text-[13px] font-medium whitespace-nowrap opacity-90 transition hover:opacity-100">
                            {{ $item->label }} <x-icon name="chevron-down" class="size-3.5" />
                        </button>
                        <div x-cloak x-show="open" x-transition.origin.top class="absolute top-full left-0 w-56 rounded-brand bg-white p-2 text-ink shadow-lift">
                            @foreach ($item->children as $child)
                                <a href="{{ $child->href() }}" target="{{ $child->target }}" class="block rounded-brand-sm px-3 py-2 text-sm hover:bg-surface">{{ $child->label }}</a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ $item->href() }}" target="{{ $item->target }}" @if ($item->isCurrent()) aria-current="page" @endif
                       @class([
                           'relative rounded-full px-2.5 py-2 text-[13px] font-medium whitespace-nowrap transition',
                           'text-accent' => $item->isCurrent(),
                           'opacity-90 hover:text-accent hover:opacity-100' => ! $item->isCurrent(),
                       ])>
                        {{ $item->label }}
                        @if ($item->isCurrent())
                            <span class="absolute inset-x-2.5 -bottom-0.5 h-0.5 rounded-full bg-accent"></span>
                        @endif
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            @if ($secondaryText)
                {{-- On md+ the secondary button lives in the top bar; without a top bar it shows here on wide screens. --}}
                <a href="{{ $secondaryUrl }}" @class(['btn hidden border border-current/25 !py-2.5 hover:bg-current/10', 'min-[1600px]:inline-flex' => ! setting_enabled('topbar_enabled'), 'max-md:hidden' => true])>{{ $secondaryText }}</a>
            @endif
            @if ($primaryText)
                <a href="{{ $primaryUrl }}" class="btn-primary hidden !py-2.5 sm:inline-flex xl:max-[1365px]:!px-3" title="{{ $primaryText }}">
                    <x-icon name="key-round" class="size-4" /> <span class="xl:max-[1365px]:sr-only">{{ $primaryText }}</span>
                </a>
            @endif
            <button type="button" @click="menuOpen = true" class="grid size-11 place-items-center rounded-full border border-current/25 transition hover:bg-current/10 xl:hidden" aria-label="Open menu" :aria-expanded="menuOpen">
                <x-icon name="menu" class="size-5" />
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <template x-teleport="body">
        <div x-cloak x-show="menuOpen" class="fixed inset-0 z-[70] xl:hidden" @keydown.escape.window="menuOpen = false">
            <div x-show="menuOpen" x-transition.opacity class="absolute inset-0 bg-primary/70" @click="menuOpen = false"></div>
            <div x-show="menuOpen"
                 x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                 class="absolute inset-y-0 right-0 flex w-full max-w-sm flex-col overflow-y-auto bg-header text-header-ink shadow-2xl">
                <div class="flex h-[76px] shrink-0 items-center justify-between border-b border-white/10 px-6">
                    <x-front.logo :url="$logoUrl" />
                    <button type="button" @click="menuOpen = false" class="grid size-10 place-items-center rounded-full border border-current/25" aria-label="Close menu">
                        <x-icon name="x" class="size-5" />
                    </button>
                </div>
                <nav class="flex flex-col px-6 pt-2" aria-label="Mobile">
                    @foreach ($headerMenu as $item)
                        <a href="{{ $item->href() }}" target="{{ $item->target }}"
                           @class(['flex items-center justify-between border-b border-white/10 py-3.5 text-[15px] font-medium', 'text-accent' => $item->isCurrent()])>
                            {{ $item->label }} <x-icon name="chevron-right" class="size-4 opacity-50" />
                        </a>
                        @foreach ($item->children as $child)
                            <a href="{{ $child->href() }}" class="block border-b border-white/5 py-2.5 pl-4 text-sm opacity-80">{{ $child->label }}</a>
                        @endforeach
                    @endforeach
                </nav>
                <div class="mt-6 flex flex-col gap-3 px-6">
                    @if ($primaryText)
                        <a href="{{ $primaryUrl }}" class="btn-primary w-full">{{ $primaryText }}</a>
                    @endif
                    @if ($secondaryText)
                        <a href="{{ $secondaryUrl }}" class="btn w-full border border-current/25">{{ $secondaryText }}</a>
                    @endif
                </div>
                <div class="mt-6 space-y-3 px-6 pb-8 text-sm opacity-85">
                    @if ($phone)
                        <a href="{{ tel_url($phone) }}" class="flex items-center gap-2"><x-icon name="phone" class="size-4 text-accent" /> {{ $phone }}</a>
                    @endif
                    @if ($email)
                        <a href="mailto:{{ $email }}" class="flex items-center gap-2"><x-icon name="mail" class="size-4 text-accent" /> {{ $email }}</a>
                    @endif
                    @if (setting('office_address'))
                        <p class="flex items-start gap-2"><x-icon name="map-pin" class="mt-0.5 size-4 text-accent" /> {{ setting('office_address') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </template>
</header>
