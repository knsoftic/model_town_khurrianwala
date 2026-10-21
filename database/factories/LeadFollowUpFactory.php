<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadFollowUp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadFollowUp>
 */
class LeadFollowUpFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'due_date' => today()->addDays(fake()->numberBetween(-3, 7)),
            'note' => fake()->randomElement(['Share payment plan', 'Call about site visit', null]),
        ];
    }

    public function done(?string $outcome = 'Called, interested'): static
    {
        return $this->state(['done_at' => now(), 'outcome' => $outcome]);
    }
}
