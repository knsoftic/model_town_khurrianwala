import Sortable from 'sortablejs';

const recurring = ['monthly', 'quarterly'];
const intervals = { quarterly: 3 };

let uid = 0;
const nextKey = () => `stage-${Date.now()}-${uid++}`;

const round2 = (value) => Math.round((Number(value) || 0) * 100) / 100;

/**
 * Visual payment schedule builder. Mirrors App\Services\InstallmentCalculator so the
 * live totals match what the server calculates after saving.
 */
export default ({ items = [], total = 0, types = {}, defaults = {} }) => ({
    items: items.map((item) => ({ key: nextKey(), ...item })),
    total: Number(total) || 0,
    types,
    defaults,
    newType: 'monthly',

    init() {
        this.$nextTick(() => {
            Sortable.create(this.$refs.list, {
                handle: '[data-drag]',
                animation: 160,
                onEnd: ({ oldIndex, newIndex }) => {
                    if (oldIndex === newIndex) return;
                    const moved = this.items.splice(oldIndex, 1)[0];
                    this.items.splice(newIndex, 0, moved);
                },
            });
        });

        this.$watch('total', () => this.items.forEach((item) => this.syncPercent(item)));
    },

    add(type = this.newType) {
        const label = this.types[type] ?? 'Payment';
        const item = {
            key: nextKey(),
            type,
            title: label,
            amount: 0,
            occurrences: recurring.includes(type) ? 12 : 1,
            percentage: 0,
            due_after_months: this.defaults[type] ?? '',
            due_label: '',
            description: '',
            open: true,
        };
        this.items.push(item);
        this.$nextTick(() => this.$refs.list.lastElementChild?.querySelector('input[data-focus]')?.focus());
    },

    remove(index) {
        this.items.splice(index, 1);
    },

    duplicate(index) {
        this.items.splice(index + 1, 0, { ...this.items[index], key: nextKey() });
    },

    move(index, direction) {
        const target = index + direction;
        if (target < 0 || target >= this.items.length) return;
        const [item] = this.items.splice(index, 1);
        this.items.splice(target, 0, item);
    },

    changeType(item) {
        if (!recurring.includes(item.type)) item.occurrences = 1;
        if (item.due_after_months === '' || item.due_after_months === null) {
            item.due_after_months = this.defaults[item.type] ?? '';
        }
        if (!item.title || Object.values(this.types).includes(item.title)) item.title = this.types[item.type];
        this.syncPercent(item);
    },

    lineTotal(item) {
        return round2((Number(item.amount) || 0) * Math.max(1, Number(item.occurrences) || 1));
    },

    syncPercent(item) {
        item.percentage = this.total > 0 ? round2((this.lineTotal(item) / this.total) * 100) : 0;
    },

    syncAmount(item) {
        const occurrences = Math.max(1, Number(item.occurrences) || 1);
        item.amount = this.total > 0 ? round2((this.total * (Number(item.percentage) || 0)) / 100 / occurrences) : 0;
    },

    fillRemaining(item) {
        const occurrences = Math.max(1, Number(item.occurrences) || 1);
        item.amount = round2((this.lineTotal(item) + this.remaining) / occurrences);
        this.syncPercent(item);
    },

    loadTemplate() {
        if (this.items.length && !confirm('Replace the current stages with the standard 36-month template?')) return;
        if (!this.total) {
            alert('Enter the total price first.');
            return;
        }
        const pct = (p, occurrences = 1) => round2((this.total * p) / 100 / occurrences);
        const template = [
            ['booking', 'Booking', pct(5), 1, 0, 'At the time of booking'],
            ['advance', 'Advance Payment', pct(15), 1, 1, 'Within 30 days of booking'],
            ['monthly', 'Monthly Installments', pct(50, 36), 36, 1, '36 monthly installments'],
            ['after_6_months', 'After 6 Months', pct(7), 1, 6, 'Due in month 6'],
            ['after_12_months', 'After 12 Months', pct(7), 1, 12, 'Due in month 12'],
            ['end_payment', 'End / Possession Payment', 0, 1, 36, 'At possession'],
        ];
        this.items = template.map(([type, title, amount, occurrences, due, label]) => ({
            key: nextKey(),
            type,
            title,
            amount,
            occurrences,
            percentage: 0,
            due_after_months: due,
            due_label: label,
            description: '',
            open: false,
        }));
        this.fillRemaining(this.items[this.items.length - 1]);
        this.items.forEach((item) => this.syncPercent(item));
    },

    get scheduled() {
        return round2(this.items.reduce((sum, item) => sum + this.lineTotal(item), 0));
    },

    get remaining() {
        return round2(this.total - this.scheduled);
    },

    get allocatedPercent() {
        return this.total > 0 ? round2((this.scheduled / this.total) * 100) : 0;
    },

    get remainingPercent() {
        return round2(100 - this.allocatedPercent);
    },

    get installmentItems() {
        return this.items.filter((item) => recurring.includes(item.type));
    },

    get installmentCount() {
        return this.installmentItems.reduce((sum, item) => sum + Math.max(1, Number(item.occurrences) || 1), 0);
    },

    get installmentAmount() {
        return Number(this.installmentItems[0]?.amount) || 0;
    },

    get duration() {
        return this.items.reduce((max, item) => {
            const start = item.due_after_months === '' || item.due_after_months === null ? null : Number(item.due_after_months);
            if (start === null) return max;
            const occurrences = Math.max(1, Number(item.occurrences) || 1);
            const last = start + (recurring.includes(item.type) ? (occurrences - 1) * (intervals[item.type] ?? 1) : 0);
            return Math.max(max, last);
        }, 0);
    },

    get balanced() {
        return Math.abs(this.remaining) < 1;
    },

    format(value) {
        return new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 }).format(Number(value) || 0);
    },
});
