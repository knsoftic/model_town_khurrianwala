<?php

namespace Database\Factories;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected const FIRST = ['Muhammad', 'Ahmed', 'Ali', 'Usman', 'Bilal', 'Hamza', 'Hassan', 'Imran', 'Zain', 'Saad', 'Ayesha', 'Fatima', 'Hina', 'Sana', 'Maryam', 'Zainab', 'Amna', 'Rabia', 'Kashif', 'Faisal'];

    protected const LAST = ['Khan', 'Ahmed', 'Raza', 'Malik', 'Hussain', 'Iqbal', 'Butt', 'Chaudhry', 'Sheikh', 'Qureshi', 'Javed', 'Aslam', 'Anwar', 'Siddiqui'];

    public function definition(): array
    {
        $name = fake()->randomElement(self::FIRST).' '.fake()->randomElement(self::LAST);

        return [
            'name' => $name,
            'phone' => '+92 3'.fake()->numerify('## #######'),
            'email' => fake()->boolean(70) ? fake()->unique()->safeEmail() : null,
            'budget' => fake()->randomElement(['Up to PKR 25 Lakh', 'PKR 25 – 50 Lakh', 'PKR 50 Lakh – 1 Crore', 'Above PKR 1 Crore']),
            'payment_preference' => fake()->randomElement(array_keys(Lead::PAYMENT_PREFERENCES)),
            'preferred_contact_method' => fake()->randomElement(array_keys(Lead::CONTACT_METHODS)),
            'message' => fake()->boolean(60) ? fake()->randomElement([
                'Please share the latest payment plan.',
                'Is a site visit possible this weekend?',
                'What is the lump-sum price?',
                'I am an overseas Pakistani. How can I book?',
                'Are corner plots available?',
            ]) : null,
            'source' => fake()->randomElement([LeadSource::Inquiry, LeadSource::PlotPage, LeadSource::Callback, LeadSource::Contact, LeadSource::Calculator, LeadSource::WhatsApp])->value,
            'status' => fake()->randomElement(LeadStatus::cases())->value,
            'ip_address' => fake()->ipv4(),
        ];
    }
}
