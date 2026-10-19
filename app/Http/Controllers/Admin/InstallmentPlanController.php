<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InstallmentPlanRequest;
use App\Models\InstallmentPlan;
use App\Models\Plot;
use App\Services\InstallmentPlanService;
use App\Services\PlotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstallmentPlanController extends Controller
{
    public function __construct(protected InstallmentPlanService $plans) {}

    public function index(Request $request): View
    {
        $plans = InstallmentPlan::query()
            ->with(['plot' => fn ($query) => $query->withTrashed(), 'items'])
            ->when($request->query('plot'), fn ($query, $plot) => $query->where('plot_id', $plot))
            ->when($request->query('q'), fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))
            ->orderBy(Plot::query()->select('sort_order')->whereColumn('plots.id', 'installment_plans.plot_id')->limit(1))
            ->orderByDesc('is_default')->orderBy('sort_order')
            ->paginate(25)->withQueryString();

        return view('admin.plans.index', [
            'plans' => $plans,
            'plots' => Plot::query()->ordered()->pluck('size_label', 'id'),
        ]);
    }

    public function schedules(): View
    {
        return view('admin.plans.schedules', [
            'plots' => Plot::query()->ordered()->with(['installmentPlans' => fn ($query) => $query->with('items')->orderByDesc('is_default')->orderBy('sort_order')])->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $plot = Plot::query()->find($request->query('plot'));

        $plan = new InstallmentPlan([
            'plot_id' => $plot?->id,
            'name' => $plot ? "{$plot->size_label} Installment Plan" : null,
            'total_price' => $plot?->total_price,
            'full_payment_price' => $plot?->starting_price,
            'is_active' => true,
            'is_default' => $plot && ! $plot->installmentPlans()->exists(),
            'sort_order' => 0,
        ]);
        $plan->setRelation('items', collect());

        return $this->form($plan);
    }

    public function store(InstallmentPlanRequest $request): RedirectResponse
    {
        $plan = $this->plans->save(new InstallmentPlan, $request->validated());

        return redirect()->route('admin.installment-plans.edit', $plan)->with('success', 'Installment plan created.');
    }

    public function edit(InstallmentPlan $installmentPlan): View
    {
        return $this->form($installmentPlan->load(['items', 'plot']));
    }

    public function update(InstallmentPlanRequest $request, InstallmentPlan $installmentPlan): RedirectResponse
    {
        $this->plans->save($installmentPlan, $request->validated());

        return redirect()->route('admin.installment-plans.edit', $installmentPlan)->with('success', 'Installment plan saved.');
    }

    public function destroy(InstallmentPlan $installmentPlan): RedirectResponse
    {
        $installmentPlan->delete();

        return redirect()->route('admin.installment-plans.index')->with('success', "“{$installmentPlan->name}” deleted.");
    }

    public function duplicate(InstallmentPlan $installmentPlan, PlotService $plots): RedirectResponse
    {
        $copy = $plots->copyPlan($installmentPlan);

        return redirect()->route('admin.installment-plans.edit', $copy)->with('success', 'Copy created (hidden). Adjust it and turn it on when ready.');
    }

    public function applyToPlot(InstallmentPlan $installmentPlan): RedirectResponse
    {
        $this->plans->applyToPlot($installmentPlan);

        return back()->with('success', "Plot card figures for {$installmentPlan->plot->size_label} updated from this plan.");
    }

    protected function form(InstallmentPlan $plan): View
    {
        return view('admin.plans.form', [
            'plan' => $plan,
            'plots' => Plot::query()->ordered()->get(['id', 'size_label', 'total_price', 'starting_price']),
            'types' => PaymentStageType::options(),
            'defaults' => collect(PaymentStageType::cases())->mapWithKeys(fn (PaymentStageType $type) => [$type->value => $type->defaultDueAfterMonths()])->all(),
            'items' => $plan->items->map(fn ($item) => [
                'type' => $item->type->value,
                'title' => $item->title,
                'amount' => (float) $item->amount,
                'occurrences' => (int) $item->occurrences,
                'percentage' => (float) $item->percentage,
                'due_after_months' => $item->due_after_months,
                'due_label' => $item->due_label,
                'description' => $item->description,
                'open' => false,
            ])->values()->all(),
        ]);
    }
}
