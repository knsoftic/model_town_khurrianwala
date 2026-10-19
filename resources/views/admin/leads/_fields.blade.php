{{-- Shared lead fields for "Add Lead" and "Edit details". --}}
<div class="grid gap-4 sm:grid-cols-2">
    <x-admin.input name="name" label="Full name" :value="$lead->name" required />
    <x-admin.input name="phone" type="tel" label="Phone / WhatsApp" :value="$lead->phone" required />
    <x-admin.input name="email" type="email" label="Email" :value="$lead->email" />
    <x-admin.select name="plot_id" label="Plot size" :value="$lead->plot_id" :options="$plots" placeholder="Not selected" />
    <x-admin.input name="budget" label="Budget" :value="$lead->budget" />
    <x-admin.select name="payment_preference" label="Payment preference" :value="$lead->payment_preference" :options="\App\Models\Lead::PAYMENT_PREFERENCES" placeholder="Not selected" />
    <x-admin.select name="preferred_contact_method" label="Contact by" :value="$lead->preferred_contact_method" :options="\App\Models\Lead::CONTACT_METHODS" placeholder="Any" />
    <x-admin.input name="preferred_time" label="Preferred time" :value="$lead->preferred_time" />
    <x-admin.select name="source" label="Source" :value="$lead->source" :options="\App\Enums\LeadSource::options()" required />
    <x-admin.select name="status" label="Status" :value="$lead->status" :options="\App\Enums\LeadStatus::options()" required />
    <x-admin.select name="assigned_to" label="Assigned to" :value="$lead->assigned_to" :options="$staff" placeholder="Unassigned" />
    <x-admin.input name="follow_up_date" type="date" label="Follow-up date" :value="$lead->follow_up_date?->format('Y-m-d')" />
    <x-admin.textarea name="message" label="Message / requirement" :value="$lead->message" rows="3" class="sm:col-span-2" />
</div>
