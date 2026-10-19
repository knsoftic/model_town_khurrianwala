@extends('layouts.admin')

@section('title', 'Pages')

@section('content')
    <x-admin.page-header title="Pages" description="Banner, headings, content and SEO for every page of the website. Switching a page off hides it and its menu links." />

    <x-admin.card :padding="false">
        <table class="a-table">
            <thead><tr><th>Page</th><th>Heading</th><th>URL</th><th class="text-center">Published</th><th></th></tr></thead>
            <tbody>
                @foreach ($pages as $page)
                    @php($route = \App\Http\Controllers\Admin\PageController::ROUTES[$page->slug] ?? null)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-16 shrink-0 overflow-hidden rounded-md bg-slate-100">@if ($page->banner_image)<img src="{{ media_url($page->banner_image) }}" alt="" class="size-full object-cover">@endif</div>
                                <a href="{{ route('admin.pages.edit', $page) }}" class="font-semibold text-slate-900 hover:text-gold-700">{{ $page->name }}</a>
                            </div>
                        </td>
                        <td class="max-w-72 truncate text-slate-600">{{ $page->heading ?: '—' }}</td>
                        <td class="text-xs text-slate-500">@if ($route)<a href="{{ route($route) }}" target="_blank" class="hover:underline">{{ parse_url(route($route), PHP_URL_PATH) ?: '/' }}</a>@endif</td>
                        <td class="text-center">
                            @if ($page->slug === 'home')
                                <span class="text-xs text-slate-400">Always</span>
                            @else
                                <x-admin.switch :url="route('admin.toggle', ['pages', $page->id])" :value="$page->is_active" label="Published" />
                            @endif
                        </td>
                        <td><x-admin.row-actions :edit="route('admin.pages.edit', $page)" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
@endsection
