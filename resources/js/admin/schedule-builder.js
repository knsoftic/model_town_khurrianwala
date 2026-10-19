import Sortable from 'sortablejs';

const recurring = ['monthly', 'quarterly', 'half_yearly'];
const intervals = { quarterly: 3, half_yearly: 6 };

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
            occurrences: type === 'half_yearly' ? 4 : recurring.includes(type) ? 24 : 1,
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
        if (this.items.length && !confirm('Replace the current stages with the standard template (down payment, 24 monthly installments, 6-month installments, on registry)?')) return;
        if (!this.total) {
            alert('Enter the total price first.');
            return;
        }
        const pct = (p, occurrences = 1) => round2((this.total * p) / 100 / occurrences);
        // Down payment → monthly installments → 6-month installments → final payment on registry.
        const template = [
            ['advance', 'Down Payment', pct(30), 1, 0, 'Paid upfront'],
            ['monthly', 'Monthly Installments', pct(25, 24), 24, 1, '24 monthly installments'],
            ['half_yearly', '6-Month Installments', pct(20, 4), 4, 6, 'Every 6 months'],
            ['registry', 'On Registry', 0, 1, null, 'Final payment on registry'],
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

    // The first repeating series (usually monthly) is "the installment"; a second series
    // such as 6-month installments is its own stage — same rule as InstallmentCalculator.
    get installmentItems() {
        const first = this.items.find((item) => recurring.includes(item.type));
        return first ? this.items.filter((item) => item.type === first.type) : [];
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
