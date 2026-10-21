<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LeadFieldRequest;
use App\Models\LeadField;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Sales & CRM → Lead Form Fields: the fields agents fill in when they add a lead.
 */
class LeadFieldController extends Controller
{
    public function index(): View
    {
        return view('admin.lead-fields.index', ['fields' => LeadField::query()->ordered()->get()]);
    }

    public function create(): View
    {
        return view('admin.lead-fields.form', ['field' => new LeadField(['type' => 'text', 'is_active' => true])]);
    }

    public function store(LeadFieldRequest $request): RedirectResponse
    {
        $data = $request->fieldAttributes();

        LeadField::query()->create($data + [
            'key' => LeadField::keyFor($data['label']),
            'sort_order' => LeadField::nextSortOrder(),
        ]);

        return redirect()->route('admin.lead-fields.index')->with('success', "Field “{$data['label']}” added to the lead form.");
    }

    public function edit(LeadField $leadField): View
    {
        return view('admin.lead-fields.form', ['field' => $leadField]);
    }

    public function update(LeadFieldRequest $request, LeadField $leadField): RedirectResponse
    {
        $leadField->update($request->fieldAttributes());

        return redirect()->route('admin.lead-fields.index')->with('success', "Field “{$leadField->label}” saved.");
    }

    /**
     * Only fields added by the admin can be deleted; values already saved on leads are kept.
     */
    public function destroy(LeadField $leadField): RedirectResponse
    {
        abort_if($leadField->is_system, 403, 'Built-in fields cannot be deleted. Switch them off instead.');

        $leadField->delete();

        return redirect()->route('admin.lead-fields.index')->with('success', "Field “{$leadField->label}” deleted.");
    }
}
