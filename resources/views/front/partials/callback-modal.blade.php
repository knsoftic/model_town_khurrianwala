<div x-data x-cloak x-show="$store.callback.open" class="fixed inset-0 z-[80] grid place-items-center p-4" role="dialog" aria-modal="true" aria-labelledby="callback-title"
     @keydown.escape.window="$store.callback.open = false" @open-callback.window="$store.callback.show($event.detail?.plot ?? null)">
    <div x-show="$store.callback.open" x-transition.opacity class="absolute inset-0 bg-primary/70 backdrop-blur-sm" @click="$store.callback.open = false"></div>

    <div x-show="$store.callback.open" x-transition.scale.95 class="relative w-full max-w-md overflow-hidden rounded-brand-lg bg-white shadow-2xl">
        <div class="relative bg-primary px-6 pt-6 pb-5 text-white">
            <div class="absolute inset-0 bg-grid-light opacity-50"></div>
            <button type="button" @click="$store.callback.open = false" class="absolute top-4 right-4 grid size-9 place-items-center rounded-full bg-white/10 hover:bg-white/20" aria-label="Close">
                <x-icon name="x" class="size-4" />
            </button>
            <span class="relative grid size-11 place-items-center rounded-full bg-accent text-primary"><x-icon name="phone-call" class="size-5" /></span>
            <h2 id="callback-title" class="relative mt-4 text-2xl font-semibold">Request a Callback</h2>
            <p class="relative mt-1 text-sm text-white/70">Leave your number and our sales team will call you back.</p>
        </div>

        <form x-data="ajaxForm" @submit.prevent="submit" action="{{ route('callback.store') }}" method="POST" class="space-y-4 p-6" novalidate>
            @csrf
            <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
            <input type="hidden" name="plot_id" :value="$store.callback.plot ?? ''">
            <input type="hidden" name="page_url" value="{{ request()->fullUrl() }}">

            <template x-if="success">
                <div class="py-6 text-center">
                    <span class="mx-auto grid size-14 place-items-center rounded-full bg-emerald-100 text-emerald-600"><x-icon name="check" class="size-7" stroke="2.5" /></span>
                    <p class="mt-4 font-medium text-primary" x-text="success"></p>
                    <button type="button" class="btn-outline mt-6" @click="reset(); $store.callback.open = false">Close</button>
                </div>
            </template>

            <div x-show="!success" class="space-y-4">
                <div>
                    <label for="cb-name" class="field-label">Your name</label>
                    <input id="cb-name" name="name" type="text" required autocomplete="name" class="field" :aria-invalid="!!errors.name">
                    <p x-show="errors.name" x-text="errors.name" class="field-error"></p>
                </div>
                <div>
                    <label for="cb-phone" class="field-label">Phone / WhatsApp</label>
                    <input id="cb-phone" name="phone" type="tel" required autocomplete="tel" placeholder="+92 3XX XXXXXXX" class="field" :aria-invalid="!!errors.phone">
                    <p x-show="errors.phone" x-text="errors.phone" class="field-error"></p>
                </div>
                <div>
                    <label for="cb-time" class="field-label">Best time to call <span class="text-ink/40">(optional)</span></label>
                    <select id="cb-time" name="preferred_time" class="field">
                        <option value="">Any time</option>
                        <option>Morning (9 AM – 12 PM)</option>
                        <option>Afternoon (12 PM – 4 PM)</option>
                        <option>Evening (4 PM – 8 PM)</option>
                    </select>
                </div>
                <p x-show="error" x-text="error" class="rounded-brand-sm bg-rose-50 px-3 py-2 text-sm text-rose-700"></p>
                <button type="submit" class="btn-primary w-full" :disabled="loading">
                    <x-icon name="phone-call" class="size-4" />
                    <span x-text="loading ? 'Sending…' : 'Call Me Back'">Call Me Back</span>
                </button>
            </div>
        </form>
    </div>
</div>
