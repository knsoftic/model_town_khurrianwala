@php
    $showAbout = setting_enabled('footer_show_about', true);
    $showQuick = setting_enabled('footer_show_quick_links', true) && $footerQuickLinks->isNotEmpty();
    $showUseful = setting_enabled('footer_show_useful_links', true) && $footerUsefulLinks->isNotEmpty();
    $showContact = setting_enabled('footer_show_contact', true);
    $showNewsletter = setting_enabled('footer_show_newsletter', true);
    $copyright = strtr((string) setting('footer_copyright', '© {year} {site_name}.'), ['{year}' => now()->year, '{site_name}' => setting('site_name')]);
    $whatsapp = whatsapp_url();
@endphp

<footer class="relative overflow-hidden bg-footer text-footer-ink">
    <div class="pointer-events-none absolute inset-0 bg-grid-light opacity-40"></div>
    <div class="pointer-events-none absolute -top-40 right-0 size-[520px] rounded-full bg-accent/10 blur-3xl"></div>

    @if ($showNewsletter)
        <div class="relative border-b border-white/10">
            <div class="container-x flex flex-col gap-6 py-10 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-xl">
                    <h2 class="font-heading text-2xl font-semibold text-white">{{ setting('footer_newsletter_title') }}</h2>
                    <p class="mt-1.5 text-sm">{{ setting('footer_newsletter_text') }}</p>
                </div>
                <form x-data="ajaxForm" @submit.prevent="submit" action="{{ route('newsletter.store') }}" method="POST" class="w-full max-w-md">
                    @csrf
                    <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                    <div x-show="!success" class="flex gap-2 rounded-brand bg-white/5 p-1.5 ring-1 ring-white/15 focus-within:ring-accent">
                        <label for="newsletter-email" class="sr-only">Email address</label>
                        <input id="newsletter-email" type="email" name="email" required placeholder="Your email address"
                               class="min-w-0 flex-1 bg-transparent px-3 text-sm text-white placeholder:text-white/45 outline-none">
                        <button type="submit" class="btn-primary !py-2.5" :disabled="loading">
                            <span x-show="!loading">Subscribe</span><span x-cloak x-show="loading">Sending…</span>
                        </button>
                    </div>
                    <p x-cloak x-show="success" x-text="success" class="flex items-center gap-2 rounded-brand bg-emerald-500/15 px-4 py-3 text-sm text-emerald-200"></p>
                    <p x-cloak x-show="errors.email || error" x-text="errors.email || error" class="mt-2 text-xs text-rose-300"></p>
                    @if (session('newsletter_success'))
                        <p class="mt-2 text-sm text-emerald-300">{{ session('newsletter_success') }}</p>
                    @endif
                </form>
            </div>
        </div>
    @endif

    <div class="relative container-x grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-12">
        @if ($showAbout)
            <div class="lg:col-span-4">
                <a href="{{ route('home') }}" class="inline-block text-white">
                    <x-front.logo :url="$footerLogoUrl" />
                </a>
                @if (setting('footer_description'))
                    <p class="mt-5 max-w-sm text-sm leading-relaxed">{{ setting('footer_description') }}</p>
                @endif
                @if ($socialLinks->isNotEmpty())
                    <div class="mt-6 flex flex-wrap gap-2.5">
                        @foreach ($socialLinks as $social)
                            <a href="{{ $social->url }}" target="_blank" rel="noopener" aria-label="{{ $social->displayLabel() }}"
                               class="grid size-10 place-items-center rounded-full bg-white/5 text-white ring-1 ring-white/10 transition hover:bg-accent hover:text-primary">
                                <x-icon :name="$social->iconName()" class="size-4" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        @if ($showQuick)
            <div class="lg:col-span-2">
                <h3 class="font-sans text-sm font-semibold tracking-wider text-white uppercase">{{ setting('footer_quick_links_title') }}</h3>
                <ul class="mt-5 space-y-3 text-sm">
                    @foreach ($footerQuickLinks as $link)
                        <li><a href="{{ $link->href() }}" target="{{ $link->target }}" class="transition hover:text-accent">{{ $link->label }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($showUseful)
            <div class="lg:col-span-2">
                <h3 class="font-sans text-sm font-semibold tracking-wider text-white uppercase">{{ setting('footer_useful_links_title') }}</h3>
                <ul class="mt-5 space-y-3 text-sm">
                    @foreach ($footerUsefulLinks as $link)
                        <li><a href="{{ $link->href() }}" target="{{ $link->target }}" class="transition hover:text-accent">{{ $link->label }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($showContact)
            <div class="lg:col-span-4">
                <h3 class="font-sans text-sm font-semibold tracking-wider text-white uppercase">{{ setting('footer_contact_title') }}</h3>
                <ul class="mt-5 space-y-4 text-sm">
                    @if (setting('office_address'))
                        <li class="flex gap-3"><x-icon name="map-pin" class="mt-0.5 size-4 text-accent" /><span>{{ setting('office_address') }}</span></li>
                    @endif
                    @if (setting('phone'))
                        <li class="flex gap-3"><x-icon name="phone" class="mt-0.5 size-4 text-accent" />
                            <span><a href="{{ tel_url(setting('phone')) }}" class="hover:text-accent">{{ setting('phone') }}</a>
                                @if (setting('phone_secondary'))<br><a href="{{ tel_url(setting('phone_secondary')) }}" class="hover:text-accent">{{ setting('phone_secondary') }}</a>@endif
                            </span>
                        </li>
                    @endif
                    @if ($whatsapp)
                        <li class="flex gap-3"><x-icon name="whatsapp" class="mt-0.5 size-4 text-accent" /><a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="hover:text-accent">{{ setting('whatsapp_number') }}</a></li>
                    @endif
                    @if (setting('email'))
                        <li class="flex gap-3"><x-icon name="mail" class="mt-0.5 size-4 text-accent" />
                            <span><a href="mailto:{{ setting('email') }}" class="hover:text-accent">{{ setting('email') }}</a>
                                @if (setting('email_secondary'))<br><a href="mailto:{{ setting('email_secondary') }}" class="hover:text-accent">{{ setting('email_secondary') }}</a>@endif
                            </span>
                        </li>
                    @endif
                    @if (setting('office_hours'))
                        <li class="flex gap-3"><x-icon name="clock" class="mt-0.5 size-4 text-accent" /><span>{{ setting('office_hours') }}</span></li>
                    @endif
                    @if (setting_enabled('footer_show_map', true) && setting('google_maps_url'))
                        <li><a href="{{ setting('google_maps_url') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 font-semibold text-accent hover:underline">
                            <x-icon name="navigation" class="size-4" /> Get directions</a></li>
                    @endif
                </ul>
            </div>
        @endif
    </div>

    <div class="relative border-t border-white/10">
        <div class="container-x flex flex-col gap-3 py-6 text-xs sm:flex-row sm:items-center sm:justify-between">
            <p>{{ $copyright }}</p>
            @if (setting('footer_disclaimer'))
                <p class="max-w-2xl opacity-75 sm:text-right">{{ setting('footer_disclaimer') }}</p>
            @endif
        </div>
    </div>
</footer>
