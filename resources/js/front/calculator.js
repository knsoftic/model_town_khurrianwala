import { money, plural } from './format';

/**
 * Payment plan calculator: loads plot sizes + plans from the public API once,
 * then recalculates instantly as the visitor changes the selection.
 */
export default (apiUrl, initialPlot = null) => ({
    loading: true,
    failed: false,
    plots: [],
    plotSlug: initialPlot,
    planSlug: null,

    async init() {
        try {
            const response = await fetch(apiUrl, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error(response.statusText);
            const json = await response.json();
            this.plots = (json.data ?? []).filter((plot) => plot.plans.length > 0);
            if (!this.plots.some((plot) => plot.slug === this.plotSlug)) {
                this.plotSlug = this.plots[0]?.slug ?? null;
            }
            this.selectDefaultPlan();
        } catch {
            this.failed = true;
        } finally {
            this.loading = false;
        }

        this.$watch('plotSlug', () => this.selectDefaultPlan());
    },

    selectDefaultPlan() {
        const plans = this.plot?.plans ?? [];
        this.planSlug = (plans.find((plan) => plan.is_default) ?? plans[0])?.slug ?? null;
    },

    get plot() {
        return this.plots.find((plot) => plot.slug === this.plotSlug) ?? null;
    },

    get plan() {
        return this.plot?.plans.find((plan) => plan.slug === this.planSlug) ?? null;
    },

    sumOf(...types) {
        return (this.plan?.items ?? [])
            .filter((item) => types.includes(item.type))
            .reduce((total, item) => total + item.line_total, 0);
    },

    get recurring() {
        return (this.plan?.items ?? []).find((item) => item.is_recurring) ?? null;
    },

    /**
     * One row per stage of the selected plan, in the plan's own order and wording
     * (e.g. Down Payment, Monthly Installments, 6-Month Installments, On Registry).
     */
    get rows() {
        if (!this.plan) return [];
        return this.plan.items
            .filter((item) => item.line_total > 0)
            .map((item, index) => ({
                key: `${index}-${item.type}`,
                label: item.title,
                value: item.is_recurring ? item.amount : item.line_total,
                note: item.repeat_text ? `${item.repeat_text} = ${money(item.line_total)}` : (item.due_label ?? null),
            }));
    },

    get duration() {
        const months = this.plan?.duration_months ?? 0;
        if (!months) return '—';
        const years = Math.floor(months / 12);
        const rest = months % 12;
        const parts = [];
        if (years) parts.push(plural(years, 'year'));
        if (rest) parts.push(plural(rest, 'month'));
        return `${months} months${years ? ` (${parts.join(' ')})` : ''}`;
    },

    money,
});
