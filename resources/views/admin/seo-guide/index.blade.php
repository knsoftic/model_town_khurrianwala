@extends('layouts.admin')

@section('title', 'SEO Guide')

@section('content')
    <x-admin.page-header title="SEO Guide" description="What is already done, what still needs you, and step-by-step instructions for on-page, technical and off-page SEO." />

    <div x-data="{ copied: false }" class="mb-6 flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Private link to this guide</p>
            <p class="mt-1 truncate font-mono text-sm font-semibold text-slate-900">{{ $shareUrl }}</p>
            <p class="mt-1 text-sm text-slate-600">Send it to anyone helping with SEO. It opens only this guide, without a login or the admin menu. Signed-in staff are taken to this page.</p>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            <button type="button" class="a-btn-secondary" @click="navigator.clipboard.writeText(@js($shareUrl)); copied = true; setTimeout(() => copied = false, 2000)">
                <x-icon name="copy" class="size-4" /> <span x-text="copied ? 'Copied' : 'Copy'">Copy</span>
            </button>
            <a href="{{ $shareUrl }}" target="_blank" rel="noopener" class="a-btn-secondary"><x-icon name="external-link" class="size-4" /> Open</a>
            <button type="button" class="a-btn-ghost" @click="$store.confirm.ask({ action: @js(route('admin.seo-guide.link')), method: 'POST', title: 'Make a new link?', message: 'The current link will stop working for everyone you sent it to.', button: 'Make new link' })">
                <x-icon name="refresh-cw" class="size-4" /> New link
            </button>
        </div>
    </div>

    @include('admin.seo-guide._guide')
@endsection
