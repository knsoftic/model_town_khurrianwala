@props(['faqs'])

<div x-data="{ active: null }" {{ $attributes->class(['divide-y divide-primary/8 rounded-brand-lg border border-primary/8 bg-white']) }}>
    @foreach ($faqs as $faq)
        <div>
            <h3>
                <button type="button" @click="active = active === {{ $faq->id }} ? null : {{ $faq->id }}" :aria-expanded="active === {{ $faq->id }}"
                        class="flex w-full items-center justify-between gap-6 px-5 py-5 text-left font-sans text-[15px] font-semibold text-primary transition hover:text-accent sm:px-6">
                    <span>{{ $faq->question }}</span>
                    <span class="grid size-8 shrink-0 place-items-center rounded-full bg-surface transition" :class="active === {{ $faq->id }} && 'rotate-45 bg-accent text-primary'">
                        <x-icon name="plus" class="size-4" stroke="2.25" />
                    </span>
                </button>
            </h3>
            <div x-show="active === {{ $faq->id }}" x-collapse x-cloak>
                <div class="px-5 pb-5 text-sm leading-relaxed text-ink/70 sm:px-6">{!! nl2br(e($faq->answer)) !!}</div>
            </div>
        </div>
    @endforeach
</div>
