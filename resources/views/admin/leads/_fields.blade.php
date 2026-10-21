{{--
    Lead fields for "Add Lead" and "Edit details", built from Sales & CRM → Lead Form Fields ($fields),
    then source, status and (for staff who manage all leads) the salesperson.
--}}
<div class="grid gap-4 sm:grid-cols-2">
    @foreach ($fields as $field)
        @php
            $common = ['name' => $field->inputName(), 'label' => $field->label, 'value' => $field->value($lead), 'help' => $field->help, 'required' => $field->is_required || $field->isLocked()];
        @endphp
        @switch($field->type)
            @case('select')
                <x-admin.select :name="$common['name']" :label="$common['label']" :value="$common['value']" :help="$common['help']" :required="$common['required']"
                                :options="$field->choices()" :placeholder="$field->placeholder ?: 'Not selected'" />
                @break
            @case('textarea')
                <x-admin.textarea :name="$common['name']" :label="$common['label']" :value="$common['value']" :help="$common['help']" :required="$common['required']"
                                  rows="3" class="sm:col-span-2" :placeholder="$field->placeholder" />
                @break
            @default
                <x-admin.input :name="$common['name']" :label="$common['label']" :value="$common['value']" :help="$common['help']" :required="$common['required']"
                               :type="['phone' => 'tel', 'number' => 'number', 'email' => 'email', 'date' => 'date'][$field->type] ?? 'text'"
                               :placeholder="$field->placeholder" :step="$field->type === 'number' ? 'any' : null" />
        @endswitch
    @endforeach

    <x-admin.select name="source" label="Lead came from" :value="$lead->source" :options="\App\Enums\LeadSource::options()" required />
    <x-admin.select name="status" label="Status" :value="$lead->status" :options="\App\Enums\LeadStatus::options()" required />
    @if (auth()->user()->can('leads'))
        <x-admin.select name="assigned_to" label="Salesperson / agent" :value="$lead->assigned_to" :options="$staff" placeholder="Unassigned" />
    @endif
</div>
