<?php

namespace Database\Factories;

use App\Models\LeadField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadField>
 */
class LeadFieldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $label = ucfirst(fake()->unique()->words(2, true));

        return [
            'key' => fn (array $attributes) => LeadField::keyFor($attributes['label']),
            'label' => $label,
            'type' => 'text',
            'is_system' => false,
            'is_required' => false,
            'show_in_list' => false,
            'is_active' => true,
            'sort_order' => LeadField::nextSortOrder(),
        ];
    }

    /**
     * A dropdown with these choices.
     *
     * @param  array<int, string>  $options
     */
    public function select(array $options): static
    {
        return $this->state(['type' => 'select', 'options' => $options]);
    }
}
