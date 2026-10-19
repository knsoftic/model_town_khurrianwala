<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PlotStatus;
use App\Http\Controllers\Controller;
use App\Models\Plot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlotAvailabilityController extends Controller
{
    public function index(): View
    {
        return view('admin.plots.availability', [
            'plots' => Plot::query()->ordered()->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plots' => ['required', 'array'],
            'plots.*.status' => ['required', Rule::enum(PlotStatus::class)],
            'plots.*.total_units' => ['required', 'integer', 'min:0', 'max:1000000'],
            'plots.*.sold_units' => ['required', 'integer', 'min:0', 'max:1000000'],
            'plots.*.is_active' => ['boolean'],
        ]);

        $errors = [];
        foreach ($data['plots'] as $id => $values) {
            if ($values['sold_units'] > $values['total_units']) {
                $errors["plots.{$id}.sold_units"] = 'Sold plots cannot be more than total plots.';
            }
        }
        if ($errors) {
            return back()->withInput()->withErrors($errors);
        }

        $plots = Plot::query()->whereKey(array_keys($data['plots']))->get();
        foreach ($plots as $plot) {
            $plot->update($data['plots'][$plot->id]);
        }

        return back()->with('success', 'Availability updated.');
    }

    public function featured(): View
    {
        return view('admin.plots.featured', [
            'plots' => Plot::query()->ordered()->get(),
        ]);
    }
}
