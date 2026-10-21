<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PakistanTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_app_runs_on_pakistan_time(): void
    {
        $this->assertSame('Asia/Karachi', config('app.timezone'));
        $this->assertSame('+05:00', now()->format('P'));
    }

    public function test_old_utc_times_move_forward_five_hours_and_dates_stay(): void
    {
        $lead = Lead::factory()->create(['follow_up_date' => '2026-03-10', 'last_contacted_at' => null]);
        DB::table('leads')->where('id', $lead->id)->update(['created_at' => '2026-03-09 21:30:00', 'updated_at' => '2026-03-09 21:30:00']);
        $followUp = $lead->followUps()->create(['due_date' => '2026-03-11', 'due_time' => '15:00']);
        DB::table('lead_follow_ups')->where('id', $followUp->id)->update(['done_at' => '2026-03-11 10:05:00']);

        $followUpDate = $lead->fresh()->follow_up_date->toDateString();

        $migration = require database_path('migrations/2026_10_21_000000_switch_times_to_pakistan_time.php');
        $migration->up();

        $lead = Lead::query()->findOrFail($lead->id);
        $this->assertSame('2026-03-10 02:30:00', $lead->created_at->format('Y-m-d H:i:s'));
        $this->assertNull($lead->last_contacted_at);
        $this->assertSame($followUpDate, $lead->follow_up_date->toDateString());

        $followUp->refresh();
        $this->assertSame('2026-03-11 15:05:00', $followUp->done_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-03-11', $followUp->due_date->toDateString());
        $this->assertStringStartsWith('15:00', (string) $followUp->due_time);

        $migration->down();
        $this->assertSame('2026-03-09 21:30:00', Lead::query()->findOrFail($lead->id)->created_at->format('Y-m-d H:i:s'));
    }
}
