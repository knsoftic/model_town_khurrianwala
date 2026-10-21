@extends('layouts.admin')

@section('title', 'SEO Guide')

@section('content')
    <x-admin.page-header title="SEO Guide" description="Kya ho chuka hai, kis cheez par aap ka kaam baaqi hai, aur on-page, technical aur off-page SEO ki step-by-step rehnumai." />

    <div x-data="{ copied: false }" class="mb-6 flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Is guide ka private link</p>
            <p class="mt-1 truncate font-mono text-sm font-semibold text-slate-900">{{ $shareUrl }}</p>
            <p class="mt-1 text-sm text-slate-600">Yeh link SEO mein madad karne wale kisi bhi shakhs ko bhejein. Yeh sirf yehi guide kholta hai — na login chahiye, na admin menu nazar aata hai. Jo staff sign in hai woh seedha is page par aata hai.</p>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            <button type="button" class="a-btn-secondary" @click="navigator.clipboard.writeText(@js($shareUrl)); copied = true; setTimeout(() => copied = false, 2000)">
                <x-icon name="copy" class="size-4" /> <span x-text="copied ? 'Copy ho gaya' : 'Copy'">Copy</span>
            </button>
            <a href="{{ $shareUrl }}" target="_blank" rel="noopener" class="a-btn-secondary"><x-icon name="external-link" class="size-4" /> Kholein</a>
            <button type="button" class="a-btn-ghost" @click="$store.confirm.ask({ action: @js(route('admin.seo-guide.link')), method: 'POST', title: 'Naya link banayein?', message: 'Purana link un sab ke liye band ho jayega jinhein aap ne bheja tha.', button: 'Naya link banayein' })">
                <x-icon name="refresh-cw" class="size-4" /> Naya link
            </button>
        </div>
    </div>

    @include('admin.seo-guide._guide')
@endsection
