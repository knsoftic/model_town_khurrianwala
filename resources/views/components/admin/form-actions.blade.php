@props(['cancel' => null, 'label' => 'Save Changes'])

<div {{ $attributes->class(['sticky bottom-0 z-10 -mx-4 mt-6 flex items-center justify-end gap-2 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8']) }}>
    {{ $slot }}
    @if ($cancel)
        <a href="{{ $cancel }}" class="a-btn-secondary">Cancel</a>
    @endif
    <button type="submit" class="a-btn-primary"><x-icon name="check" class="size-4" /> {{ $label }}</button>
</div>
