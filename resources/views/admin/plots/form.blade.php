@extends('layouts.admin')

@php
    $editing = $plot->exists;
    $currency = setting('currency_symbol', 'PKR');
@endphp

@section('title', $editing ? 'Edit '.$plot->size_label : 'Add Plot Size')

@section('breadcrumbs')
    <a href="{{ route('admin.plots.index') }}" class="hover:text-slate-900">Plot Sizes</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $editing ? $plot->size_label : 'New' }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.plots.update', $plot) : route('admin.plots.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.page-header :title="$editing ? 'Edit '.$plot->size_label : 'Add Plot Size'" description="Everything on the plot card and the plot's page comes from this form.">
            <x-slot:actions>
                @if ($editing)
                    <a href="{{ route('plots.show', $plot) }}" target="_blank" class="a-btn-secondary"><x-icon name="external-link" class="size-4" /> View on website</a>
                @endif
                <button class="a-btn-primary"><x-icon name="check" class="size-4" /> Save</button>
            </x-slot:actions>
        </x-admin.page-header>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <x-admin.card title="Basic information">
                    <div class="grid gap-4 sm:grid-cols-2" x-data="slugField(@js($plot->slug ?? ''), @js($editing))">
                        <x-admin.input name="size_label" label="Plot size" :value="$plot->size_label" required placeholder="e.g. 5 Marla" x-on:input="follow($event.target.value)" />
                        <div>
                            <label for="f-slug" class="a-label">Page URL</label>
                            <div class="flex rounded-lg shadow-xs">
                                <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 px-3 text-xs text-slate-500">/plots/</span>
                                <input id="f-slug" name="slug" x-model="slug" @input="touched = true" class="a-input rounded-l-none" placeholder="5-marla">
                            </div>
                            @error('slug')<p class="a-error">{{ $message }}</p>@enderror
                        </div>
                        <x-admin.input name="title" label="Title" :value="$plot->title" required placeholder="5 Marla Residential Plot" class="sm:col-span-2" />
                        <x-admin.select name="category" label="Category" :value="$plot->category" :options="\App\Models\Plot::CATEGORIES" />
                        <x-admin.select name="location_id" label="Location" :value="$plot->location_id" :options="$locations" placeholder="No location" />
                        <x-admin.input name="dimensions" label="Dimensions (ft)" :value="$plot->dimensions" placeholder="25x45" />
                        <x-admin.input name="area_sqft" type="number" min="0" label="Area (sq ft)" :value="$plot->area_sqft" />
                        <x-admin.textarea name="short_description" label="Short description (cards & meta)" :value="$plot->short_description" rows="2" maxlength="500" class="sm:col-span-2" />
                    </div>
                </x-admin.card>

                <x-admin.card title="Price & payment summary" description="Shown on the plot card and the plot page. The full schedule lives in Installment Plans.">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <x-admin.input name="starting_price" type="number" step="1" min="0" label="Starting from (lump sum)" :prefix="$currency" :value="$plot->starting_price ? (float) $plot->starting_price : null" />
                        <x-admin.input name="total_price" type="number" step="1" min="0" label="Total price (on installments)" :prefix="$currency" :value="$plot->total_price ? (float) $plot->total_price : null" />
                        <x-admin.input name="booking_amount" type="number" step="1" min="0" label="Booking amount" :prefix="$currency" :value="$plot->booking_amount ? (float) $plot->booking_amount : null" />
                        <x-admin.input name="down_payment" type="number" step="1" min="0" label="Down / advance payment" :prefix="$currency" :value="$plot->down_payment ? (float) $plot->down_payment : null" />
                        <x-admin.input name="installment_amount" type="number" step="1" min="0" label="Installment amount" :prefix="$currency" :value="$plot->installment_amount ? (float) $plot->installment_amount : null" />
                        <x-admin.input name="installment_count" type="number" min="0" label="Number of installments" :value="$plot->installment_count" />
                        <x-admin.select name="payment_frequency" label="Installment frequency" :value="$plot->payment_frequency" :options="\App\Models\Plot::FREQUENCIES" />
                        <x-admin.input name="after_6_months_payment" type="number" step="1" min="0" label="After 6 months" :prefix="$currency" :value="$plot->after_6_months_payment ? (float) $plot->after_6_months_payment : null" />
                        <x-admin.input name="after_12_months_payment" type="number" step="1" min="0" label="After 12 months" :prefix="$currency" :value="$plot->after_12_months_payment ? (float) $plot->after_12_months_payment : null" />
                        <x-admin.input name="end_payment" type="number" step="1" min="0" label="End / possession payment" :prefix="$currency" :value="$plot->end_payment ? (float) $plot->end_payment : null" />
                        <x-admin.input name="development_charges" type="number" step="1" min="0" label="Development charges" :prefix="$currency" :value="$plot->development_charges ? (float) $plot->development_charges : null" />
                        <x-admin.input name="possession_charges" type="number" step="1" min="0" label="Possession charges" :prefix="$currency" :value="$plot->possession_charges ? (float) $plot->possession_charges : null" />
                    </div>
                    @if ($editing && $plot->installmentPlans->isNotEmpty())
                        <p class="mt-4 flex items-start gap-2 rounded-lg bg-sky-50 px-3 py-2 text-xs text-sky-800">
                            <x-icon name="info" class="mt-0.5 size-4" /> Tip: open an installment plan and press <strong>“Copy figures to plot card”</strong> to fill these from its schedule automatically.
                        </p>
                    @endif
                </x-admin.card>

                <x-admin.card title="Full description">
                    <x-admin.rich name="full_description" :value="$plot->full_description" help="Shown on the plot's page under “About This Plot”." />
                </x-admin.card>

                <x-admin.card title="Images" description="Main image is used on cards. Gallery images appear on the plot page.">
                    <x-admin.image name="image" label="Main image" :current="$plot->image" aspect="aspect-[4/3]" />

                    <div class="mt-6">
                        <span class="a-label">Gallery images</span>
                        @if ($editing && $plot->images->isNotEmpty())
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4" x-data="sortable(@js(route('admin.reorder', 'plot-images')))">
                                @foreach ($plot->images as $image)
                                    <div data-id="{{ $image->id }}" class="group overflow-hidden rounded-lg border border-slate-200 bg-white">
                                        <div class="relative aspect-[4/3] bg-slate-100">
                                            <img src="{{ media_url($image->path) }}" alt="" class="size-full object-cover">
                                            <span data-drag class="absolute top-1.5 left-1.5 grid size-7 cursor-grab place-items-center rounded-md bg-white/90 text-slate-600 shadow" title="Drag to reorder"><x-icon name="grip-vertical" class="size-4" /></span>
                                            <button type="button" class="absolute top-1.5 right-1.5 grid size-7 place-items-center rounded-md bg-white/90 text-rose-600 shadow hover:bg-rose-600 hover:text-white" title="Remove image"
                                                    @click="$store.confirm.ask({ action: @js(route('admin.plots.images.destroy', [$plot, $image])), title: 'Remove this image?', message: 'Unsaved changes on this page will be lost.', button: 'Remove' })">
                                                <x-icon name="trash" class="size-3.5" />
                                            </button>
                                        </div>
                                        <input name="captions[{{ $image->id }}]" value="{{ $image->caption }}" placeholder="Caption (optional)" class="w-full border-0 border-t border-slate-100 px-2 py-1.5 text-xs focus:ring-0">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <div x-data="{ files: [] }" class="mt-3">
                            <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500 transition hover:border-gold-500 hover:bg-gold-50/40">
                                <x-icon name="images" class="size-7 text-slate-400" />
                                <span><span class="font-semibold text-gold-700">Choose images</span> to add to the gallery (up to 20 at once)</span>
                                <input type="file" name="gallery[]" multiple accept="image/jpeg,image/png,image/webp" class="sr-only"
                                       @change="files = [...$event.target.files].map(f => ({ name: f.name, url: URL.createObjectURL(f) }))">
                            </label>
                            <div x-show="files.length" x-cloak class="mt-3 grid grid-cols-4 gap-2 sm:grid-cols-6">
                                <template x-for="file in files" :key="file.url"><img :src="file.url" :alt="file.name" class="aspect-square w-full rounded-md object-cover"></template>
                            </div>
                            @error('gallery.*')<p class="a-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.seo-fields :seo="$plot->seo" />
            </div>

            <div class="space-y-6">
                <x-admin.card title="Publishing">
                    <div class="space-y-4">
                        <x-admin.select name="status" label="Status badge" :value="$plot->status" :options="\App\Enums\PlotStatus::options()" help="Sold Out and Coming Soon hide the “Book” button." />
                        <x-admin.toggle name="is_active" label="Show on website" :checked="$plot->is_active" />
                        <x-admin.toggle name="is_featured" label="Featured plot" :checked="$plot->is_featured" help="Gets a “Featured” ribbon." />
                        <x-admin.toggle name="is_demo_price" label="Mark prices as demo" :checked="$plot->is_demo_price" help="Shows a “Demo Price” label." />
                        <x-admin.input name="price_note" label="Price note" :value="$plot->price_note" placeholder="e.g. Prices valid till 31 Dec" />
                        <x-admin.input name="sort_order" type="number" min="0" label="Display order" :value="$plot->sort_order" />
                    </div>
                </x-admin.card>

                <x-admin.card title="Availability">
                    <div class="grid grid-cols-2 gap-4" x-data="{ total: @js((int) $plot->total_units), sold: @js((int) $plot->sold_units) }">
                        <x-admin.input name="total_units" type="number" min="0" label="Total plots" :value="(int) $plot->total_units" x-model.number="total" />
                        <x-admin.input name="sold_units" type="number" min="0" label="Sold" :value="(int) $plot->sold_units" x-model.number="sold" />
                        <p class="col-span-2 rounded-lg bg-slate-50 px-3 py-2 text-sm">Available: <strong x-text="Math.max(0, (total || 0) - (sold || 0))"></strong></p>
                    </div>
                </x-admin.card>

                @if ($editing)
                    <x-admin.card title="Installment plans" :padding="false">
                        <x-slot:actions>
                            <a href="{{ route('admin.installment-plans.create', ['plot' => $plot->id]) }}" class="a-btn-ghost a-btn-sm"><x-icon name="plus" class="size-3.5" /> Add</a>
                        </x-slot:actions>
                        <ul class="divide-y divide-slate-100">
                            @forelse ($plot->installmentPlans as $plan)
                                @php($summary = $plan->summary())
                                <li>
                                    <a href="{{ route('admin.installment-plans.edit', $plan) }}" class="block px-5 py-3 hover:bg-slate-50">
                                        <span class="flex items-center gap-2 text-sm font-semibold text-slate-900">
                                            {{ $plan->name }}
                                            @if ($plan->is_default)<span class="a-badge bg-gold-100 text-gold-700 ring-gold-500/30">Default</span>@endif
                                            @unless ($plan->is_active)<span class="a-badge bg-slate-100 text-slate-500 ring-slate-300">Hidden</span>@endunless
                                        </span>
                                        <span class="mt-0.5 block text-xs text-slate-500">{{ money($summary->totalPrice, false) }} · {{ count($summary->stages) }} stages · {{ $summary->durationMonths }} months</span>
                                        @unless ($summary->isBalanced())
                                            <span class="mt-1 block text-xs font-medium text-amber-700">Schedule is {{ money(abs($summary->remaining), false) }} {{ $summary->remaining > 0 ? 'short of' : 'over' }} the total</span>
                                        @endunless
                                    </a>
                                </li>
                            @empty
                                <li class="px-5 py-6 text-center text-sm text-slate-500">No installment plan yet.</li>
                            @endforelse
                        </ul>
                    </x-admin.card>

                    <x-admin.card title="Danger zone">
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" form="duplicate-plot" class="a-btn-secondary a-btn-sm"><x-icon name="copy" class="size-3.5" /> Duplicate</button>
                            <button type="button" class="a-btn-ghost a-btn-sm text-rose-600" @click="$store.confirm.ask({ action: @js(route('admin.plots.destroy', $plot)), title: @js('Delete '.$plot->size_label.'?'), message: 'It will be hidden from the website. Leads and bookings are kept.' })"><x-icon name="trash" class="size-3.5" /> Delete</button>
                        </div>
                    </x-admin.card>
                @endif
            </div>
        </div>

        <x-admin.form-actions :cancel="route('admin.plots.index')" :label="$editing ? 'Save Plot Size' : 'Create Plot Size'" />
    </form>

    @if ($editing)
        <form id="duplicate-plot" method="POST" action="{{ route('admin.plots.duplicate', $plot) }}" class="hidden">@csrf</form>
    @endif
@endsection
