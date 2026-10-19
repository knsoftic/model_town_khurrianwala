@props([
    'plots' => collect(),
    'selected' => null,
    'source' => 'inquiry',
    'compact' => false,
    'dark' => false,
    'title' => null,
    'intro' => null,
    'submitLabel' => 'Submit Inquiry',
])

@php
    $budgets = collect(preg_split('/\r\n|\r|\n/', (string) setting('budget_options')))->map(fn ($line) => trim($line))->filter();
    $uid = 'lf'.substr(md5($source.($selected ?? '').$compact), 0, 6);
    $label = $dark ? 'mb-1.5 block text-sm font-medium text-white/80' : 'field-label';
@endphp

<form x-data="ajaxForm" @submit.prevent="submit" action="{{ route('leads.store') }}" method="POST" novalidate {{ $attributes->class(['relative']) }}>
    @csrf
    <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
    <input type="hidden" name="source" value="{{ $source }}">
    <input type="hidden" name="page_url" value="{{ request()->fullUrl() }}">

    @if ($title)
        <h3 @class(['text-2xl font-semibold', 'text-white' => $dark, 'text-primary' => ! $dark])>{{ $title }}</h3>
    @endif
    @if ($intro)
        <p @class(['mt-1.5 text-sm', 'text-white/70' => $dark, 'text-ink/60' => ! $dark])>{{ $intro }}</p>
    @endif

    @if (session('form_success'))
        <div class="mt-5 rounded-brand bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('form_success') }}</div>
    @endif

    <div x-cloak x-show="success" class="mt-5 rounded-brand bg-emerald-50 p-6 text-center">
        <span class="mx-auto grid size-12 place-items-center rounded-full bg-emerald-500 text-white"><x-icon name="check" class="size-6" stroke="2.5" /></span>
        <p class="mt-3 font-medium text-emerald-900" x-text="success"></p>
        <button type="button" @click="reset()" class="mt-4 text-sm font-semibold text-emerald-800 underline underline-offset-4">Send another inquiry</button>
    </div>

    <div x-show="!success" @class(['grid gap-4', 'mt-5' => $title || $intro, 'sm:grid-cols-2' => ! $compact])>
        <div>
            <label for="{{ $uid }}-name" class="{{ $label }}">Full name <span class="text-rose-500">*</span></label>
            <input id="{{ $uid }}-name" name="name" type="text" required autocomplete="name" class="field" value="{{ old('name') }}" :aria-invalid="!!errors.name">
            <p x-show="errors.name" x-text="errors.name" class="field-error"></p>
            @error('name')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="{{ $uid }}-phone" class="{{ $label }}">Phone / WhatsApp <span class="text-rose-500">*</span></label>
            <input id="{{ $uid }}-phone" name="phone" type="tel" required autocomplete="tel" placeholder="+92 3XX XXXXXXX" class="field" value="{{ old('phone') }}" :aria-invalid="!!errors.phone">
            <p x-show="errors.phone" x-text="errors.phone" class="field-error"></p>
            @error('phone')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        @unless ($compact)
            <div>
                <label for="{{ $uid }}-email" class="{{ $label }}">Email</label>
                <input id="{{ $uid }}-email" name="email" type="email" autocomplete="email" class="field" value="{{ old('email') }}" :aria-invalid="!!errors.email">
                <p x-show="errors.email" x-text="errors.email" class="field-error"></p>
            </div>
        @endunless
        <div>
            <label for="{{ $uid }}-plot" class="{{ $label }}">Plot size</label>
            <select id="{{ $uid }}-plot" name="plot_id" class="field">
                <option value="">Not sure yet</option>
                @foreach ($plots as $plot)
                    <option value="{{ $plot->id }}" @selected((string) old('plot_id', $selected) === (string) $plot->id)>{{ $plot->size_label }}</option>
                @endforeach
            </select>
        </div>
        @unless ($compact)
            <div>
                <label for="{{ $uid }}-budget" class="{{ $label }}">Budget</label>
                <select id="{{ $uid }}-budget" name="budget" class="field">
                    <option value="">Select budget</option>
                    @foreach ($budgets as $budget)
                        <option @selected(old('budget') === $budget)>{{ $budget }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="{{ $uid }}-pay" class="{{ $label }}">Payment preference</label>
                <select id="{{ $uid }}-pay" name="payment_preference" class="field">
                    @foreach (\App\Models\Lead::PAYMENT_PREFERENCES as $value => $text)
                        <option value="{{ $value }}" @selected(old('payment_preference') === $value)>{{ $text }}</option>
                    @endforeach
                </select>
            </div>
            <fieldset class="sm:col-span-2">
                <legend class="{{ $label }}">Preferred contact method</legend>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\Models\Lead::CONTACT_METHODS as $value => $text)
                        <label class="cursor-pointer">
                            <input type="radio" name="preferred_contact_method" value="{{ $value }}" class="peer sr-only" @checked(old('preferred_contact_method', 'whatsapp') === $value)>
                            <span @class([
                                'inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition peer-checked:border-accent peer-checked:bg-accent peer-checked:text-primary peer-focus-visible:ring-2 peer-focus-visible:ring-accent',
                                'border-white/20 text-white/80' => $dark,
                                'border-primary/15 text-ink/70' => ! $dark,
                            ])>
                                <x-icon :name="['phone' => 'phone', 'whatsapp' => 'whatsapp', 'email' => 'mail'][$value]" class="size-4" /> {{ $text }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <div class="sm:col-span-2">
                <label for="{{ $uid }}-message" class="{{ $label }}">Message</label>
                <textarea id="{{ $uid }}-message" name="message" rows="3" class="field" placeholder="Tell us what you are looking for…">{{ old('message') }}</textarea>
                <p x-show="errors.message" x-text="errors.message" class="field-error"></p>
            </div>
        @endunless

        <div @class(['sm:col-span-2' => ! $compact])>
            <p x-show="error" x-text="error" class="mb-3 rounded-brand-sm bg-rose-50 px-3 py-2 text-sm text-rose-700"></p>
            @error('form')<p class="mb-3 rounded-brand-sm bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ $message }}</p>@enderror
            <button type="submit" class="btn-primary w-full !py-3.5" :disabled="loading">
                <x-icon name="send" class="size-4" x-show="!loading" />
                <x-icon name="loader-circle" class="size-4 animate-spin" x-cloak x-show="loading" />
                <span x-text="loading ? 'Sending…' : @js($submitLabel)">{{ $submitLabel }}</span>
            </button>
            <p @class(['mt-3 flex items-center justify-center gap-1.5 text-xs', 'text-white/50' => $dark, 'text-ink/50' => ! $dark])>
                <x-icon name="lock" class="size-3.5" /> Your details are only used to contact you about your inquiry.
            </p>
        </div>
    </div>
</form>
