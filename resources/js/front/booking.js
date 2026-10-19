import { money, plural } from './format';

/**
 * Booking form: the plan dropdown follows the chosen plot size, and a summary
 * of the selected plan's schedule is shown beside the form.
 */
export default (plots, initialPlot = null, initialPlan = null) => ({
    plots,
    plotId: initialPlot ? String(initialPlot) : '',
    planId: initialPlan ? String(initialPlan) : '',

    init() {
        if (this.plotId && !this.planId) this.pickDefaultPlan();
        this.$watch('plotId', () => this.pickDefaultPlan());
    },

    pickDefaultPlan() {
        const plans = this.plot?.plans ?? [];
        this.planId = String((plans.find((plan) => plan.is_default) ?? plans[0])?.id ?? '');
    },

    get plot() {
        return this.plots.find((plot) => String(plot.id) === String(this.plotId)) ?? null;
    },

    get plan() {
        return this.plot?.plans.find((plan) => String(plan.id) === String(this.planId)) ?? null;
    },

    occurrenceLabel(item) {
        if (item.occurrences <= 1) return '';
        return item.repeat_text ?? `× ${plural(item.occurrences, 'month')}`;
    },

    money,
});
