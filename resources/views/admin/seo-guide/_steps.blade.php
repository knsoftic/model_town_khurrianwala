{{--
    Numbered how-to steps (text comes from _guide, in Roman Urdu). Each step: title, text, optional points (list), code (text to copy)
    and links [label, url, admin?] — admin links open the admin panel (sign-in needed).
--}}
<ol class="space-y-0">
    @foreach ($steps as $step)
        <li class="relative flex gap-4">
            @unless ($loop->last)
                <span class="absolute top-9 bottom-0 left-4 w-px -translate-x-1/2 bg-slate-200" aria-hidden="true"></span>
            @endunless
            <span class="relative grid size-8 shrink-0 place-items-center rounded-full bg-navy-900 text-sm font-bold text-white">{{ $loop->iteration }}</span>
            <div class="min-w-0 flex-1 pb-7">
                <h3 class="pt-1 font-semibold text-slate-900">{{ $step['title'] }}</h3>
                <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $step['text'] }}</p>
                @if (! empty($step['points']))
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm leading-relaxed text-slate-600 marker:text-slate-400">
                        @foreach ($step['points'] as $point)
                            <li>{{ $point }}</li>
                        @endforeach
                    </ul>
                @endif
                @if (! empty($step['code']))
                    <p class="mt-2 rounded-lg bg-slate-900 px-3 py-2 font-mono text-xs break-all text-slate-100">{{ $step['code'] }}</p>
                @endif
                @if (! empty($step['links']))
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($step['links'] as [$label, $url, $isAdmin])
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="a-btn-secondary a-btn-sm">
                                <x-icon :name="$isAdmin ? 'lock' : 'external-link'" class="size-3.5 text-slate-400" /> {{ $label }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </li>
    @endforeach
</ol>
