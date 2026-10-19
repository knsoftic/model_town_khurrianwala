@props(['url', 'value' => false, 'field' => 'is_active', 'label' => null])

{{-- Instant on/off switch (PATCH via AJAX to the generic toggle endpoint). --}}
<button type="button" x-data="toggle(@js($url), @js((bool) $value), @js($field))" @click="flip()" :aria-pressed="on"
        :class="on ? 'bg-emerald-500' : 'bg-slate-300'" :disabled="busy"
        {{ $attributes->class(['relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition disabled:opacity-60']) }}
        title="{{ $label ?? 'Toggle' }}" aria-label="{{ $label ?? 'Toggle' }}">
    <span :class="on ? 'translate-x-4' : 'translate-x-0.5'" class="inline-block size-4 rounded-full bg-white shadow transition"></span>
</button>
