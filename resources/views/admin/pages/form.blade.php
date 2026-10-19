@extends('layouts.admin')

@php
    $route = \App\Http\Controllers\Admin\PageController::ROUTES[$page->slug] ?? null;
    $options = \App\Models\Page::SETTINGS[$page->slug] ?? [];
    $isHome = $page->slug === 'home';
@endphp

@section('title', 'Edit '.$page->name)

@section('breadcrumbs')
    <a href="{{ route('admin.pages.index') }}" class="hover:text-slate-900">Pages</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $page->name }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <x-admin.page-header :title="$page->name" :description="$isHome ? 'The homepage layout is managed under Homepage Sections; here you set its SEO.' : 'Banner, headings, content and SEO for this page.'">
            <x-slot:actions>
                @if ($route)<a href="{{ route($route) }}" target="_blank" class="a-btn-secondary"><x-icon name="external-link" class="size-4" /> View page</a>@endif
                <button class="a-btn-primary"><x-icon name="check" class="size-4" /> Save</button>
            </x-slot:actions>
        </x-admin.page-header>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                @unless ($isHome)
                    <x-admin.card title="Banner">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-admin.input name="eyebrow" label="Small label" :value="$page->eyebrow" />
                            <x-admin.input name="heading" label="Heading" :value="$page->heading" />
                            <x-admin.textarea name="subheading" label="Subheading" :value="$page->subheading" rows="2" class="sm:col-span-2" />
                            <x-admin.image name="banner_image" label="Banner background image" :current="$page->banner_image" class="sm:col-span-2" help="Wide image, 1920×640 or larger." />
                        </div>
                    </x-admin.card>

                    <x-admin.card title="Content">
                        <x-admin.rich name="content" :value="$page->content" help="Main text of the page (shown under the banner)." />
                    </x-admin.card>

                    @if (in_array($page->slug, ['about'], true))
                        <x-admin.card title="Images & video">
                            <div class="grid gap-6">
                                <x-admin.image name="image" label="Main image" :current="$page->image" aspect="aspect-[4/3]" />
                                <div>
                                    <span class="a-label">Photo strip</span>
                                    @if (! empty($page->images))
                                        <div class="mb-3 grid grid-cols-3 gap-3 sm:grid-cols-5">
                                            @foreach ($page->images as $path)
                                                <label class="group relative block overflow-hidden rounded-lg border border-slate-200" x-data="{ remove: false }">
                                                    <img src="{{ media_url($path) }}" alt="" class="aspect-square w-full object-cover" :class="remove && 'opacity-30'">
                                                    <input type="checkbox" name="remove_gallery[]" value="{{ $path }}" x-model="remove" class="absolute top-1.5 right-1.5 size-4 rounded border-slate-300 text-rose-600">
                                                    <span x-show="remove" class="absolute inset-x-0 bottom-0 bg-rose-600 py-0.5 text-center text-[10px] font-semibold text-white">Will be removed</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                    <input type="file" name="gallery[]" multiple accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold hover:file:bg-slate-200">
                                    <p class="a-help">Tick an image to remove it. Add new ones with the file picker.</p>
                                </div>
                                <x-admin.input name="video_url" type="url" label="YouTube / Vimeo video link" :value="$page->video_url" help="Leave empty to use the featured video from Gallery → Videos." />
                            </div>
                        </x-admin.card>
                    @endif

                    <x-admin.card title="Call to action button">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-admin.input name="cta_text" label="Button text" :value="$page->cta_text" />
                            <x-admin.input name="cta_url" label="Button link" :value="$page->cta_url" placeholder="/plots" />
                        </div>
                    </x-admin.card>
                @endunless

                <x-admin.seo-fields :seo="$page->seo" />
            </div>

            <div class="space-y-6">
                <x-admin.card title="Page">
                    <div class="space-y-4">
                        <x-admin.input name="name" label="Page name (admin)" :value="$page->name" required />
                        @unless ($isHome)
                            <x-admin.toggle name="is_active" label="Published" :checked="$page->is_active" help="Turning off hides the page and its menu links." />
                        @endunless
                        @foreach ($options as $key => $label)
                            <x-admin.toggle :name="'settings['.$key.']'" :label="$label" :checked="$page->setting($key, true)" />
                        @endforeach
                    </div>
                </x-admin.card>
            </div>
        </div>

        <x-admin.form-actions :cancel="route('admin.pages.index')" />
    </form>
@endsection
