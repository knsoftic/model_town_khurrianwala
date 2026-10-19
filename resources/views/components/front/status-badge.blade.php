@props(['status'])

<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold tracking-wide uppercase ring-1 ring-inset backdrop-blur', $status->badgeClasses()]) }}>
    <span class="size-1.5 rounded-full bg-current"></span>
    {{ $status->label() }}
</span>
