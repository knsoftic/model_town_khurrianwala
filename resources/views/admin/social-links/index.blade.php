@extends('layouts.admin')

@section('title', 'Social Links')

@php($platforms = collect(\App\Models\SocialLink::PLATFORMS)->map(fn ($meta) => $meta[0])->all())

@section('content')
    <x-admin.page-header title="Social Links" description="Icons shown in the top bar and footer. Drag to reorder." />

    <div class="grid gap-6 xl:grid-cols-3">
        <ol class="space-y-2 xl:col-span-2" x-data="sortable(@js(route('admin.reorder', 'social-links')))">
            @forelse ($links as $link)
                <li data-id="{{ $link->id }}" class="a-card" x-data="{ open: false }">
                    <div class="flex items-center gap-3 p-3">
                        <span data-drag class="cursor-grab px-1 text-slate-400 hover:text-slate-700"><x-icon name="grip-vertical" class="size-5" /></span>
                        <span class="grid size-10 place-items-center rounded-full bg-navy-900 text-gold-400"><x-icon :name="$link->iconName()" class="size-5" /></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-900">{{ $link->displayLabel() }}</p>
                            <a href="{{ $link->url }}" target="_blank" rel="noopener" class="block truncate text-xs text-sky-700 hover:underline">{{ $link->url }}</a>
                        </div>
                        <x-admin.switch :url="route('admin.toggle', ['social-links', $link->id])" :value="$link->is_active" />
                        <button type="button" class="a-icon-btn" @click="open = !open" title="Edit"><x-icon name="pencil" class="size-4" /></button>
                        <button type="button" class="a-icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="Remove" @click="$store.confirm.ask({ action: @js(route('admin.social-links.destroy', $link)), title: 'Remove this link?', button: 'Remove' })"><x-icon name="trash" class="size-4" /></button>
                    </div>
                    <form x-show="open" x-collapse method="POST" action="{{ route('admin.social-links.update', $link) }}" class="grid gap-3 border-t border-slate-100 bg-slate-50/60 p-4 sm:grid-cols-3">
                        @csrf @method('PUT')
                        <x-admin.select name="platform" label="Platform" :value="$link->platform" :options="$platforms" />
                        <x-admin.input name="label" label="Label (optional)" :value="$link->label" />
                        <x-admin.input name="url" type="url" label="URL" :value="$link->url" required />
                        <x-admin.toggle name="is_active" label="Visible" :checked="$link->is_active" />
                        <div class="flex justify-end sm:col-span-2"><button class="a-btn-primary a-btn-sm"><x-icon name="check" class="size-3.5" /> Save</button></div>
                    </form>
                </li>
            @empty
                <li class="a-card"><x-admin.empty icon="share-2" title="No social links yet" /></li>
            @endforelse
        </ol>

        <form method="POST" action="{{ route('admin.social-links.store') }}" class="xl:sticky xl:top-24 xl:self-start">
            @csrf
            <x-admin.card title="Add social link">
                <div class="space-y-4">
                    <x-admin.select name="platform" label="Platform" :options="$platforms" />
                    <x-admin.input name="url" type="url" label="Profile URL" required placeholder="https://facebook.com/yourpage" />
                    <x-admin.input name="label" label="Label (optional)" />
                    <x-admin.toggle name="is_active" label="Visible" :checked="true" />
                </div>
                <button class="a-btn-gold mt-4 w-full"><x-icon name="plus" class="size-4" /> Add link</button>
            </x-admin.card>
        </form>
    </div>
@endsection
