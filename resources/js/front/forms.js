/**
 * Submits any <form x-data="ajaxForm"> with fetch and shows Laravel validation
 * errors inline. Without JavaScript the form still posts normally.
 */
export default () => ({
    loading: false,
    success: '',
    error: '',
    reference: '',
    errors: {},

    async submit() {
        const form = this.$el.closest('form') ?? this.$el;
        this.loading = true;
        this.error = '';
        this.errors = {};

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
                credentials: 'same-origin',
            });
            const data = await response.json().catch(() => ({}));

            if (response.ok) {
                this.success = data.message ?? 'Thank you! We will be in touch shortly.';
                this.reference = data.reference ?? '';
                form.reset();
                this.$dispatch('form-submitted', { form: form.id });
                return;
            }

            if (response.status === 422) {
                this.errors = Object.fromEntries(Object.entries(data.errors ?? {}).map(([key, messages]) => [key, messages[0]]));
                this.error = 'Please check the highlighted fields.';
                this.$nextTick(() => form.querySelector('[aria-invalid="true"]')?.focus());
            } else if (response.status === 419) {
                this.error = 'Your session expired. Please refresh the page and try again.';
            } else {
                this.error = data.message ?? 'Something went wrong. Please try again or call us directly.';
            }
        } catch {
            this.error = 'Network error. Please check your connection and try again.';
        } finally {
            this.loading = false;
        }
    },

    reset() {
        this.success = '';
        this.reference = '';
        this.errors = {};
        this.error = '';
    },
});
