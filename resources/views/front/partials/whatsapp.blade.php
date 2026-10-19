@php
    $url = whatsapp_url();
    $left = setting('whatsapp_button_position') === 'left';
    $color = preg_match('/^#[0-9a-f]{3,6}$/i', (string) setting('whatsapp_button_color')) ? setting('whatsapp_button_color') : '#25D366';
@endphp

@if ($url && setting_enabled('whatsapp_button_enabled', true))
    <a href="{{ $url }}" target="_blank" rel="noopener"
       @class(['group fixed bottom-5 z-40 flex items-center gap-3 sm:bottom-6', 'left-5 flex-row-reverse sm:left-6' => $left, 'right-5 sm:right-6' => ! $left])
       aria-label="{{ setting('whatsapp_button_label', 'Chat on WhatsApp') }}">
        <span class="pointer-events-none hidden translate-y-1 rounded-full bg-white px-4 py-2 text-sm font-semibold text-primary opacity-0 shadow-lift transition group-hover:translate-y-0 group-hover:opacity-100 sm:block">
            {{ setting('whatsapp_button_label', 'Chat on WhatsApp') }}
        </span>
        <span class="relative grid size-14 place-items-center rounded-full text-white shadow-lift transition group-hover:scale-105" style="background-color: {{ $color }}">
            <span class="absolute inset-0 animate-ping rounded-full opacity-30" style="background-color: {{ $color }}"></span>
            <x-icon name="whatsapp" class="relative size-7" stroke="2" />
        </span>
    </a>
@endif
