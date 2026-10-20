import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Sortable from 'sortablejs';
import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';
import scheduleBuilder from './admin/schedule-builder';
import richEditor from './admin/rich-editor';
import videoForm from './admin/video-form';

Chart.register(ArcElement, BarController, BarElement, CategoryScale, DoughnutController, Filler, Legend, LinearScale, LineController, LineElement, PointElement, Tooltip);
Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, sans-serif';
Chart.defaults.color = '#64748b';

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

async function request(url, method = 'POST', body = {}) {
    const response = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(body),
        credentials: 'same-origin',
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(data.message ?? 'Request failed');
    return data;
}

Alpine.plugin(collapse);
Alpine.data('scheduleBuilder', scheduleBuilder);
Alpine.data('richEditor', richEditor);
Alpine.data('videoForm', videoForm);

Alpine.store('toast', {
    items: [],
    show(message, type = 'success') {
        const id = Date.now() + Math.random();
        this.items.push({ id, message, type });
        setTimeout(() => (this.items = this.items.filter((item) => item.id !== id)), 3500);
    },
});

Alpine.store('confirm', {
    open: false,
    title: '',
    message: '',
    action: '',
    method: 'DELETE',
    button: 'Delete',
    ask({ action, title = 'Are you sure?', message = 'This action cannot be undone.', method = 'DELETE', button = 'Delete' }) {
        Object.assign(this, { action, title, message, method, button, open: true });
    },
});

/** AJAX on/off switch for is_active / is_featured columns. */
Alpine.data('toggle', (url, value, field = 'is_active') => ({
    on: Boolean(value),
    busy: false,
    async flip() {
        if (this.busy) return;
        this.busy = true;
        const previous = this.on;
        this.on = !this.on;
        try {
            const data = await request(url, 'PATCH', { field, value: this.on });
            Alpine.store('toast').show(data.message ?? 'Saved');
        } catch (error) {
            this.on = previous;
            Alpine.store('toast').show(error.message, 'error');
        } finally {
            this.busy = false;
        }
    },
}));

/** Drag-and-drop ordering that saves the new order immediately. */
Alpine.data('sortable', (url) => ({
    init() {
        Sortable.create(this.$el, {
            handle: '[data-drag]',
            animation: 160,
            onEnd: async () => {
                const ids = [...this.$el.querySelectorAll(':scope > [data-id]')].map((el) => el.dataset.id);
                try {
                    const data = await request(url, 'POST', { ids });
                    Alpine.store('toast').show(data.message ?? 'Order saved');
                } catch (error) {
                    Alpine.store('toast').show(error.message, 'error');
                }
            },
        });
    },
}));

/** Inline select that PATCHes a single field (e.g. lead status). */
Alpine.data('inlineSelect', (url, field) => ({
    async save(event) {
        try {
            const data = await request(url, 'PATCH', { [field]: event.target.value });
            Alpine.store('toast').show(data.message ?? 'Updated');
            if (data.badge && this.$refs.badge) this.$refs.badge.outerHTML = data.badge;
        } catch (error) {
            Alpine.store('toast').show(error.message, 'error');
        }
    },
}));

Alpine.data('chart', (config) => ({
    init() {
        const instance = new Chart(this.$refs.canvas, {
            ...config,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: config.type === 'doughnut', position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } } },
                ...(config.type !== 'doughnut'
                    ? { scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef2f7' } } } }
                    : { cutout: '68%' }),
                ...(config.options ?? {}),
            },
        });
        this.$el._chart = instance;
    },
}));

/** Image file input with instant preview. */
Alpine.data('imageInput', (current = null) => ({
    preview: current,
    removed: false,
    pick(event) {
        const [file] = event.target.files;
        if (!file) return;
        this.removed = false;
        this.preview = URL.createObjectURL(file);
    },
    clear() {
        this.preview = null;
        this.removed = true;
        this.$refs.file.value = '';
    },
}));

/** Repeater rows (landmarks, etc.). */
Alpine.data('repeater', (rows = [], blank = {}) => ({
    rows: rows.length ? rows : [],
    add() {
        this.rows.push({ ...blank });
    },
    remove(index) {
        this.rows.splice(index, 1);
    },
}));

/** Select-all checkbox for bulk actions in tables. */
Alpine.data('bulkSelect', () => ({
    selected: [],
    toggleAll(event) {
        this.selected = event.target.checked ? [...this.$root.querySelectorAll('[data-row-id]')].map((el) => el.value) : [];
    },
}));

/** Slug field that follows the title until edited manually. */
Alpine.data('slugField', (initial = '', locked = false) => ({
    slug: initial,
    touched: locked || initial !== '',
    follow(value) {
        if (this.touched) return;
        this.slug = value
            .toString()
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    },
}));

window.Alpine = Alpine;
Alpine.start();
