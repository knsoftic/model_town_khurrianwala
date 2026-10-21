<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\LeadSource;
use App\Models\BookingRequest;
use App\Models\ContactMessage;
use App\Models\Lead;
use App\Models\Plot;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Demo leads, booking requests and messages (local/staging only) so the
 * dashboard charts and reports have something to show.
 */
class DemoCrmSeeder extends Seeder
{
    public function run(): void
    {
        if (Lead::query()->exists()) {
            return;
        }

        $plots = Plot::query()->with('installmentPlans')->get();
        $staff = User::query()->pluck('id')->all();
        $weights = ['3-marla' => 3, '5-marla' => 5, '10-marla' => 3, '1-kanal' => 1];
        $pool = $plots->flatMap(fn (Plot $plot) => array_fill(0, $weights[$plot->slug] ?? 1, $plot))->values();

        for ($i = 0; $i < 140; $i++) {
            $created = Carbon::now()->subDays((int) round(pow(mt_rand(0, 1000) / 1000, 1.6) * 360))->setTime(mt_rand(9, 21), mt_rand(0, 59));
            $plot = $pool->random();

            $lead = Lead::factory()->create([
                'plot_id' => $plot->id,
                'assigned_to' => mt_rand(0, 100) < 60 ? $staff[array_rand($staff)] : null,
                'created_at' => $created,
                'updated_at' => $created,
            ]);

            if (mt_rand(0, 100) < 30) {
                $lead->followUps()->create(['user_id' => $lead->assigned_to, 'due_date' => Carbon::now()->addDays(mt_rand(-5, 14))]);
            }

            if ($created->greaterThan(now()->subDays(4))) {
                $lead->update(['status' => 'new']);
            }
        }

        $statuses = [BookingStatus::Pending, BookingStatus::Pending, BookingStatus::Contacted, BookingStatus::OnHold, BookingStatus::Approved, BookingStatus::Approved, BookingStatus::Rejected];

        for ($i = 0; $i < 24; $i++) {
            $plot = $pool->random();
            $plan = $plot->installmentPlans->first();
            $created = Carbon::now()->subDays(mt_rand(0, 300));
            $lead = Lead::factory()->create(['plot_id' => $plot->id, 'source' => LeadSource::Booking->value, 'status' => 'new', 'created_at' => $created, 'updated_at' => $created]);

            BookingRequest::query()->forceCreate([
                'reference' => sprintf('BK-%s-%04d', $created->format('ym'), $i + 1),
                'plot_id' => $plot->id,
                'installment_plan_id' => $plan?->id,
                'lead_id' => $lead->id,
                'name' => $lead->name,
                'phone' => $lead->phone,
                'email' => $lead->email,
                'city' => fake()->randomElement(['Faisalabad', 'Lahore', 'Khurrianwala', 'Jaranwala', 'Sheikhupura']),
                'status' => $statuses[array_rand($statuses)]->value,
                'message' => 'Demo booking request.',
                'created_at' => $created,
                'updated_at' => $created,
            ]);
        }

        foreach (['Site visit timing', 'Overseas booking', 'Transfer procedure', 'Commercial plots'] as $i => $subject) {
            ContactMessage::query()->forceCreate([
                'name' => fake()->randomElement(['Kashif Iqbal', 'Rabia Anwar', 'Saad Malik', 'Amna Butt']),
                'email' => fake()->safeEmail(),
                'phone' => '+92 3'.fake()->numerify('## #######'),
                'subject' => $subject,
                'message' => 'This is a demo contact message about: '.strtolower($subject).'.',
                'read_at' => $i > 1 ? now() : null,
                'created_at' => now()->subDays($i * 3),
            ]);
        }
    }
}
