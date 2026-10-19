{{-- Live preview of the brand colours; reads the colour inputs of the form it sits in. --}}
<div x-data="{
        c: {},
        refresh() { this.$root.closest('form').querySelectorAll('input[name^=color_]').forEach(i => this.c[i.name] = i.value); this.c.radius = (this.$root.closest('form').querySelector('[name=border_radius]')?.value || 14) + 'px'; },
    }"
     x-init="$nextTick(() => refresh())" @input.window.debounce.60ms="refresh()" class="a-card mb-6 overflow-hidden">
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
        <p class="text-sm font-semibold text-slate-900">Live preview</p>
        <a href="{{ route('home') }}" target="_blank" class="text-xs font-semibold text-gold-700 hover:underline">Open website</a>
    </div>
    <div class="p-5" :style="`background:${c.color_background}`">
        <div class="overflow-hidden shadow-lg" :style="`border-radius:${c.radius}`">
            <div class="flex items-center justify-between px-5 py-3 text-sm font-semibold" :style="`background:${c.color_header_bg}; color:${c.color_header_text}`">
                <span>{{ setting('site_name') }}</span>
                <span class="hidden gap-4 opacity-90 sm:flex"><span>Home</span><span>Plot Sizes</span><span>Contact</span></span>
                <span class="px-3 py-1.5 text-xs" :style="`background:${c.color_button}; color:${c.color_button_text}; border-radius:${c.radius}`">Book Your Plot</span>
            </div>
            <div class="px-6 py-8" :style="`background:${c.color_primary}; color:${c.color_secondary}`">
                <p class="text-xs font-semibold tracking-[0.2em] uppercase" :style="`color:${c.color_accent}`">Bookings open</p>
                <p class="mt-2 font-serif text-2xl font-semibold">Secure Your Future With Your Own Plot</p>
                <div class="mt-4 flex gap-2">
                    <span class="px-4 py-2 text-xs font-semibold" :style="`background:${c.color_button}; color:${c.color_button_text}; border-radius:${c.radius}`">Primary button</span>
                    <span class="px-4 py-2 text-xs font-semibold" :style="`background:${c.color_button_hover}; color:${c.color_button_text}; border-radius:${c.radius}`">Hover state</span>
                </div>
            </div>
            <div class="bg-white px-6 py-5" :style="`color:${c.color_text}`">
                <p class="text-sm">Body text uses the text colour. <span class="font-semibold" :style="`color:${c.color_accent}`">Accent highlights</span> stand out.</p>
            </div>
            <div class="px-6 py-4 text-xs" :style="`background:${c.color_footer_bg}; color:${c.color_footer_text}`">© {{ now()->year }} {{ setting('site_name') }} · Footer text</div>
        </div>
    </div>
</div>
