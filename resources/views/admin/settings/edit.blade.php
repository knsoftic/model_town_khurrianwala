@extends('layouts.admin')

@section('title', $definition['label'].' Settings')

@php
    $isSuper = auth()->user()->isSuperAdmin();
    $scriptsAllowed = (bool) config('cms.allow_custom_scripts');
@endphp

@section('content')
    <x-admin.page-header :title="$group === 'appearance' ? 'Appearance' : 'Website Settings'" :description="$definition['description'] ?? ''" />

    <div class="grid gap-6 lg:grid-cols-[220px_1fr]">
        <nav class="scrollbar-none -mx-4 flex gap-1 overflow-x-auto px-4 lg:mx-0 lg:flex-col lg:px-0" aria-label="Settings sections">
            @foreach ($groups as $key => $item)
                <a href="{{ route('admin.settings.edit', $key) }}" @class([
                    'flex shrink-0 items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition',
                    'bg-navy-900 text-white' => $key === $group,
                    'text-slate-600 hover:bg-white hover:text-slate-900' => $key !== $group,
                ])>
                    <x-icon :name="$item['icon'] ?? 'settings'" class="size-4" /> {{ $item['label'] }}
                </a>
            @endforeach
            <a href="{{ route('admin.social-links.index') }}" class="flex shrink-0 items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-white hover:text-slate-900">
                <x-icon name="share-2" class="size-4" /> Social Links
            </a>
        </nav>

        <form method="POST" action="{{ route('admin.settings.update', $group) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            @if ($group === 'appearance')
                @include('admin.settings._appearance-preview')
            @endif

            @if ($group === 'redirect')
                @php
                    $shortLink = url($values['redirect_path'] ?: 'leads');
                    $redirectLive = filter_var($values['redirect_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN) && filled($values['redirect_url'] ?? null);
                @endphp
                <div x-data="{ copied: false }" class="mb-6 flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Your short link</p>
                        <p class="mt-1 truncate font-mono text-lg font-semibold text-slate-900">{{ $shortLink }}</p>
                        <p @class(['mt-1 text-sm', 'text-emerald-700' => $redirectLive, 'text-amber-700' => ! $redirectLive])>
                            @if ($redirectLive)
                                Visitors are sent to <span class="break-all">{{ $values['redirect_url'] }}</span>
                            @else
                                Not active yet — add a destination link below and switch it on.
                            @endif
                        </p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <button type="button" class="a-btn-secondary" @click="navigator.clipboard.writeText(@js($shortLink)); copied = true; setTimeout(() => copied = false, 2000)">
                            <x-icon name="copy" class="size-4" /> <span x-text="copied ? 'Copied' : 'Copy'">Copy</span>
                        </button>
                        <a href="{{ $shortLink }}" target="_blank" rel="noopener" class="a-btn-secondary"><x-icon name="external-link" class="size-4" /> Open</a>
                    </div>
                </div>
            @endif

            <x-admin.card :title="$definition['label']">
                <div class="grid gap-5 sm:grid-cols-2">
                    @foreach ($fields as $key => $field)
                        @if (($field['super'] ?? false) && ! $isSuper)
                            <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500 sm:col-span-2">
                                <x-icon name="lock" class="inline size-4" /> <strong>{{ $field['label'] }}</strong> can only be changed by a Super Admin.
                            </div>
                            @continue
                        @endif
                        <x-admin.field :name="$key" :field="$field" :value="$values[$key] ?? null" />
                        @if (($field['super'] ?? false) && ! $scriptsAllowed)
                            <p class="-mt-3 text-xs text-amber-700 sm:col-span-2">Custom scripts are switched off on this server (CMS_ALLOW_CUSTOM_SCRIPTS=false), so this field is saved but not printed.</p>
                        @endif
                    @endforeach
                </div>
            </x-admin.card>

            @if ($group === 'notifications')
                <div class="mt-4 flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4">
                    <p class="text-sm text-slate-600">Save first, then send a test email to {{ $values['notification_email'] ?: auth()->user()->email }}.</p>
                    <button type="submit" form="test-email" class="a-btn-secondary"><x-icon name="send" class="size-4" /> Send test email</button>
                </div>
            @endif

            <x-admin.form-actions label="Save Settings" />
        </form>
    </div>

    @if ($group === 'notifications')
        <form id="test-email" method="POST" action="{{ route('admin.settings.test-email') }}" class="hidden">@csrf</form>
    @endif
@endsection
