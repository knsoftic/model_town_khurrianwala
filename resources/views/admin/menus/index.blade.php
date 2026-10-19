@extends('layouts.admin')

@section('title', 'Menus')

@section('content')
    <x-admin.page-header title="Menus" description="Header navigation and footer link columns. Drag to reorder; changes save instantly. Header buttons are under Settings → Header." />

    <div class="mb-5 flex gap-2 overflow-x-auto">
        @foreach (\App\Models\NavigationItem::LOCATIONS as $key => $label)
            <a href="{{ route('admin.menus.index', ['location' => $key]) }}" @class(['a-btn shrink-0 border', 'border-navy-900 bg-navy-900 text-white' => $location === $key, 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' => $location !== $key])>
                {{ $label }} <span class="opacity-70">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <ol class="space-y-2" x-data="sortable(@js(route('admin.reorder', 'menus')))">
                @forelse ($items as $item)
                    <li data-id="{{ $item->id }}" class="a-card" x-data="{ open: false }">
                        <div class="flex items-center gap-3 p-3">
                            <span data-drag class="cursor-grab px-1 text-slate-400 hover:text-slate-700"><x-icon name="grip-vertical" class="size-5" /></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-900">{{ $item->label }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $item->route_name ? 'Page: '.($routes[$item->route_name] ?? $item->route_name) : $item->url }}@if ($item->target === '_blank') · new tab @endif</p>
                            </div>
                            <x-admin.switch :url="route('admin.toggle', ['menus', $item->id])" :value="$item->is_active" label="Visible" />
                            <button type="button" class="a-icon-btn" @click="open = !open" title="Edit"><x-icon name="pencil" class="size-4" /></button>
                            <button type="button" class="a-icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="Remove" @click="$store.confirm.ask({ action: @js(route('admin.menus.destroy', $item)), title: @js('Remove “'.$item->label.'”?'), button: 'Remove' })"><x-icon name="trash" class="size-4" /></button>
                        </div>
                        <form x-show="open" x-collapse method="POST" action="{{ route('admin.menus.update', $item) }}" class="border-t border-slate-100 bg-slate-50/60 p-4">
                            @csrf @method('PUT')
                            @include('admin.menus._item-form', ['parents' => $items])
                            <div class="mt-3 flex justify-end"><button class="a-btn-primary a-btn-sm"><x-icon name="check" class="size-3.5" /> Save link</button></div>
                        </form>
                        @if ($item->children->isNotEmpty())
                            <ol class="space-y-1 border-t border-slate-100 py-2 pr-3 pl-12" x-data="sortable(@js(route('admin.reorder', 'menus')))">
                                @foreach ($item->children as $child)
                                    <li data-id="{{ $child->id }}" class="flex items-center gap-3 rounded-lg bg-slate-50 px-3 py-2">
                                        <span data-drag class="cursor-grab text-slate-400"><x-icon name="grip-vertical" class="size-4" /></span>
                                        <span class="flex-1 text-sm text-slate-700">{{ $child->label }}</span>
                                        <x-admin.switch :url="route('admin.toggle', ['menus', $child->id])" :value="$child->is_active" />
                                        <button type="button" class="a-icon-btn hover:!text-rose-600" @click="$store.confirm.ask({ action: @js(route('admin.menus.destroy', $child)), title: @js('Remove “'.$child->label.'”?'), button: 'Remove' })"><x-icon name="trash" class="size-3.5" /></button>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </li>
                @empty
                    <li class="a-card"><x-admin.empty icon="menu" title="No links in this menu yet" /></li>
                @endforelse
            </ol>
        </div>

        <form method="POST" action="{{ route('admin.menus.store') }}" class="xl:sticky xl:top-24 xl:self-start">
            @csrf
            <input type="hidden" name="location" value="{{ $location }}">
            <x-admin.card title="Add a link" :description="'To: '.\App\Models\NavigationItem::LOCATIONS[$location]">
                @include('admin.menus._item-form', ['item' => new \App\Models\NavigationItem(['target' => '_self']), 'parents' => $items])
                <button class="a-btn-gold mt-4 w-full"><x-icon name="plus" class="size-4" /> Add link</button>
            </x-admin.card>
        </form>
    </div>
@endsection
