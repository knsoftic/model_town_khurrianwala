<?php

namespace Database\Seeders;

use App\Enums\MilestoneStatus;
use App\Enums\VideoProvider;
use App\Models\DevelopmentMilestone;
use App\Models\Faq;
use App\Models\GalleryAlbum;
use App\Models\Plot;
use App\Models\ProjectFeature;
use App\Models\Testimonial;
use App\Models\Video;
use Database\Seeders\Support\DemoArt;
use Illuminate\Database\Seeder;

class ProjectContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->features();
        $this->development();
        $this->testimonials();
        $this->gallery();
        $this->videos();
        $this->faqs();
    }

    protected function features(): void
    {
        $features = [
            ['Gated Community', 'shield-check', 'A secure, gated boundary with controlled entry and exit points.'],
            ['24/7 Security', 'shield-user', 'Trained security staff on duty around the clock.'],
            ['Parks & Green Belts', 'trees', 'Landscaped parks, walking tracks and green belts in every block.'],
            ['Grand Mosque', 'mosque', 'A central mosque within walking distance of residential blocks.'],
            ['Electricity', 'zap', 'Underground or planned overhead electrification for every street.'],
            ['Water Supply', 'droplets', 'Planned water supply network with storage for every block.'],
            ['Sewerage System', 'waves-horizontal', 'A modern, planned sewerage and drainage network.'],
            ['Wide Carpeted Roads', 'road', 'Wide main boulevards and well-planned internal streets.'],
            ['Commercial Area', 'store', 'Shops, offices and daily essentials inside the community.'],
            ['Street Lights', 'lamp', 'Street lighting along main roads and residential streets.'],
            ['CCTV Surveillance', 'cctv', 'CCTV cameras at entry points and key areas.'],
            ['Boundary Wall', 'brick-wall', 'A complete boundary wall around the project.'],
        ];

        foreach ($features as $i => [$title, $icon, $description]) {
            ProjectFeature::query()->updateOrCreate(['title' => $title], ['icon' => $icon, 'description' => $description, 'sort_order' => $i, 'is_active' => true]);
        }
    }

    protected function development(): void
    {
        $milestones = [
            ['Land Development', 100, MilestoneStatus::Completed, 'land-plot', 'Leveling, demarcation and block layout completed.'],
            ['Road Construction', 80, MilestoneStatus::InProgress, 'road', 'Main boulevard complete; internal streets in progress.'],
            ['Electricity', 70, MilestoneStatus::InProgress, 'zap', 'Poles and lines installed in most residential blocks.'],
            ['Water Supply', 60, MilestoneStatus::InProgress, 'droplets', 'Main lines laid; block connections ongoing.'],
            ['Parks & Landscaping', 50, MilestoneStatus::InProgress, 'trees', 'Central park and green belts under development.'],
            ['Commercial Area', 30, MilestoneStatus::InProgress, 'store', 'Commercial block foundations and access roads started.'],
        ];

        foreach ($milestones as $i => [$title, $percentage, $status, $icon, $description]) {
            DevelopmentMilestone::query()->updateOrCreate(['title' => $title], [
                'percentage' => $percentage, 'status' => $status->value, 'icon' => $icon,
                'description' => $description.' (Demo progress)', 'sort_order' => $i, 'is_active' => true,
            ]);
        }
    }

    protected function testimonials(): void
    {
        $plots = Plot::query()->pluck('id', 'slug');

        $testimonials = [
            ['Muhammad Ahmed', 'Faisalabad', '5-marla', 'Excellent experience. The installment plan made it very easy for us to purchase our plot.'],
            ['Ayesha Khan', 'Lahore', '10-marla', 'The payment schedule was explained clearly before booking. No surprises at any stage.'],
            ['Usman Tariq', 'Khurrianwala', '3-marla', 'Affordable monthly installments and a helpful sales team. We booked our first plot here.'],
            ['Fatima Noor', 'Dubai (Overseas)', '1-kanal', 'Booking from abroad was smooth. The team shared regular development updates on WhatsApp.'],
            ['Bilal Hussain', 'Jaranwala', '5-marla', 'Visible development work and clear documents gave us confidence to invest.'],
            ['Hina Raza', 'Sheikhupura', '10-marla', 'Professional staff and transparent pricing. Highly recommended for families.'],
        ];

        foreach ($testimonials as $i => [$name, $location, $plot, $review]) {
            Testimonial::query()->updateOrCreate(['client_name' => $name], [
                'client_title' => 'Sample testimonial',
                'location' => $location,
                'plot_id' => $plots[$plot] ?? null,
                'review' => $review,
                'rating' => 5,
                'is_featured' => $i < 3,
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }
    }

    protected function gallery(): void
    {
        $albums = [
            'project' => 'Project',
            'development' => 'Development',
            'roads' => 'Roads',
            'parks' => 'Parks',
            'construction' => 'Construction',
            'events' => 'Events',
            'site-visit' => 'Site Visit',
            'possession' => 'Possession',
        ];

        $sizes = [[1200, 800], [900, 1100], [1200, 900], [1000, 1000]];
        $order = 0;

        foreach ($albums as $slug => $name) {
            $album = GalleryAlbum::query()->updateOrCreate(['slug' => $slug], ['name' => $name, 'sort_order' => $order++, 'is_active' => true]);
            $album->images()->delete();

            $count = in_array($slug, ['project', 'development', 'roads', 'parks'], true) ? 3 : 2;

            for ($i = 0; $i < $count; $i++) {
                [$w, $h] = $sizes[($order + $i) % count($sizes)];
                $path = DemoArt::save("gallery-{$slug}-{$i}", DemoArt::scene($slug, $order + $i, $w, $h));
                $album->images()->create([
                    'title' => "{$name} — demo image ".($i + 1),
                    'path' => $path,
                    'caption' => 'Demo illustration. Upload real photos from Admin → Gallery.',
                    'sort_order' => $i,
                    'is_active' => true,
                ]);

                if ($i === 0) {
                    $album->update(['cover_image' => $path]);
                }
            }
        }
    }

    protected function videos(): void
    {
        Video::query()->updateOrCreate(['title' => 'Project Walkthrough'], [
            'provider' => VideoProvider::YouTube->value,
            'url' => null,
            'thumbnail' => DemoArt::save('video-thumbnail', DemoArt::masterPlan(1280, 720, true, 33)),
            'description' => 'A guided tour of the master plan, main boulevard and development work. Add your YouTube, Vimeo or uploaded video from Admin → Gallery → Videos.',
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    protected function faqs(): void
    {
        $faqs = [
            ['Booking', 'How do I book a plot?', 'Choose a plot size and installment plan, then submit a booking request on the website or visit our office. Our sales team will confirm availability, verify your details and guide you through the official booking process.'],
            ['Booking', 'Which documents are required for booking?', 'Usually a copy of your CNIC, two passport-size photographs and the booking amount. Overseas Pakistanis can book with a NICOP or passport. Our team will confirm the exact requirements.'],
            ['Booking', 'Does submitting the online booking form reserve a plot?', 'No. The online form is a booking request only. A plot is reserved only after our team confirms availability and the official booking process is completed.'],
            ['Payments', 'What does an installment plan include?', 'Each plan lists the booking amount, advance payment, monthly or quarterly installments, any milestone payments (such as after 6 or 12 months) and the end/possession payment, with the total price.'],
            ['Payments', 'Can I pay the full amount at once?', 'Yes. A lump-sum (full payment) option is available for every plot size. Please contact our sales team for the current lump-sum price.'],
            ['Payments', 'Are development and possession charges included?', 'Development and possession charges are listed separately on each plot size and are payable as per company policy.'],
            ['Payments', 'What happens if an installment is late?', 'Please contact our office as soon as possible. Late payment terms are explained in the booking documents and are applied as per company policy.'],
            ['Possession', 'When will I get possession?', 'Possession is handed over after development of the relevant block is complete and all dues are cleared. Current progress is shown on our Development page.'],
            ['General', 'Can I transfer my plot file?', 'Yes, transfers are possible as per company policy once the required dues are cleared. Visit our office for the transfer procedure and charges.'],
            ['General', 'Can I visit the project site?', 'Yes. Contact us to schedule a site visit — our team will be happy to show you the project and the available plots.'],
        ];

        foreach ($faqs as $i => [$category, $question, $answer]) {
            Faq::query()->updateOrCreate(['question' => $question], ['category' => $category, 'answer' => $answer, 'sort_order' => $i, 'is_active' => true]);
        }
    }
}
