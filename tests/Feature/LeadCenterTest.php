<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadField;
use App\Models\LeadFollowUp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class LeadCenterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $agent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
        $this->admin = User::query()->whereHas('role', fn ($q) => $q->where('is_super', true))->firstOrFail();
        $this->agent = $this->staff('agent', ['name' => 'Agent Ali']);
    }

    public function test_admin_adds_a_dropdown_field_that_appears_on_the_add_lead_form(): void
    {
        $this->actingAs($this->admin)->post('/admin/lead-fields', [
            'label' => 'Came from?',
            'type' => 'select',
            'options' => "WhatsApp\r\nPhone call\nWhatsApp\n",
            'is_required' => '1',
            'show_in_list' => '1',
            'is_active' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/lead-fields');

        $field = LeadField::query()->where('label', 'Came from?')->firstOrFail();
        $this->assertSame('came_from', $field->key);
        $this->assertSame(['WhatsApp', 'Phone call'], $field->options);

        $this->actingAs($this->agent)->get('/admin/leads/create')->assertOk()
            ->assertSee('Came from?')
            ->assertSee('name="extra[came_from]"', false)
            ->assertSee('Location (city / area)');
    }

    public function test_agent_adds_a_lead_with_custom_fields_and_a_first_follow_up(): void
    {
        $this->travelTo(now()->setTime(11, 30));

        $this->actingAs($this->agent)->post('/admin/leads', [
            'name' => 'Usman Raza',
            'phone' => '+92 300 1112233',
            'extra' => ['location' => 'Jaranwala Road, Faisalabad'],
            'source' => 'facebook',
            'status' => 'new',
            'assigned_to' => $this->admin->id,
            'next_follow_up_date' => today()->addDay()->format('Y-m-d'),
            'next_follow_up_time' => '15:00',
            'next_follow_up_note' => 'Send 5 Marla plan',
        ])->assertSessionHasNoErrors();

        $lead = Lead::query()->where('phone', '+92 300 1112233')->firstOrFail();
        $this->assertSame($this->agent->id, $lead->created_by);
        $this->assertSame($this->agent->id, $lead->assigned_to, 'Agents cannot hand a new lead to someone else.');
        $this->assertSame(['location' => 'Jaranwala Road, Faisalabad'], $lead->extra);
        $this->assertTrue($lead->created_at->equalTo(now()->startOfSecond()));
        $this->assertSame(today()->addDay()->toDateString(), $lead->follow_up_date->toDateString());

        $followUp = $lead->followUps()->firstOrFail();
        $this->assertSame('Send 5 Marla plan', $followUp->note);
        $this->assertStringContainsString('03:00 PM', $followUp->dueLabel());

        $this->actingAs($this->agent)->get("/admin/leads/{$lead->id}")->assertOk()
            ->assertSee('Jaranwala Road, Faisalabad')
            ->assertSee('Send 5 Marla plan');
    }

    public function test_required_fields_from_the_form_builder_are_enforced_with_their_label(): void
    {
        LeadField::factory()->create(['label' => 'Profession', 'is_required' => true]);

        $this->actingAs($this->agent)->post('/admin/leads', ['name' => 'X', 'phone' => '0300', 'source' => 'phone', 'status' => 'new'])
            ->assertSessionHasErrors(['extra.profession' => 'The Profession field is required.']);
    }

    public function test_agents_only_see_and_change_their_own_leads(): void
    {
        $own = Lead::factory()->create(['name' => 'Own Lead', 'created_by' => $this->agent->id, 'status' => 'new']);
        $assigned = Lead::factory()->create(['name' => 'Assigned Lead', 'assigned_to' => $this->agent->id]);
        $other = Lead::factory()->create(['name' => 'Someone Else Lead']);

        $this->actingAs($this->agent)->get('/admin/leads')->assertOk()
            ->assertSee('Own Lead')->assertSee('Assigned Lead')->assertDontSee('Someone Else Lead');

        $this->actingAs($this->agent)->get("/admin/leads/{$other->id}")->assertForbidden();
        $this->actingAs($this->agent)->patch("/admin/leads/{$other->id}/quick", ['status' => 'contacted'])->assertForbidden();
        $this->actingAs($this->agent)->delete("/admin/leads/{$own->id}")->assertForbidden();
        $this->actingAs($this->agent)->post('/admin/leads/bulk', ['ids' => [$other->id], 'action' => 'delete'])->assertForbidden();
        $this->actingAs($this->agent)->patchJson("/admin/leads/{$own->id}/quick", ['assigned_to' => $this->admin->id])->assertForbidden();
        $this->actingAs($this->agent)->get('/admin/lead-fields')->assertForbidden();

        $this->actingAs($this->agent)->patchJson("/admin/leads/{$own->id}/quick", ['status' => 'interested'])->assertOk();
        $this->assertSame(LeadStatus::Interested, $own->fresh()->status);

        $csv = $this->actingAs($this->agent)->get('/admin/leads/export/csv')->streamedContent();
        $this->assertStringContainsString('Own Lead', $csv);
        $this->assertStringContainsString('Assigned Lead', $csv);
        $this->assertStringNotContainsString('Someone Else Lead', $csv);

        $this->actingAs($this->admin)->get('/admin/leads')->assertSee('Someone Else Lead')->assertSee('Own Lead');
        $this->assertTrue($assigned->isVisibleTo($this->agent));
    }

    public function test_marking_a_follow_up_done_records_the_result_and_books_the_next_one(): void
    {
        $lead = Lead::factory()->create(['created_by' => $this->agent->id, 'status' => 'new']);
        $first = $lead->followUps()->create(['due_date' => today()->subDay(), 'user_id' => $this->agent->id]);
        $this->assertSame('overdue', $first->state());

        $this->actingAs($this->agent)->get('/admin/leads?follow_up=overdue')->assertSee($lead->name);

        $this->actingAs($this->agent)->patch("/admin/follow-ups/{$first->id}", [
            'outcome' => 'Called — wants a site visit on Sunday',
            'next_date' => today()->addDays(3)->format('Y-m-d'),
            'next_note' => 'Confirm the visit',
        ])->assertSessionHasNoErrors();

        $first->refresh();
        $this->assertNotNull($first->done_at);
        $this->assertSame($this->agent->id, $first->done_by);
        $this->assertSame('Called — wants a site visit on Sunday', $first->outcome);

        $lead->refresh();
        $this->assertSame(LeadStatus::Contacted, $lead->status);
        $this->assertNotNull($lead->last_contacted_at);
        $this->assertSame(today()->addDays(3)->toDateString(), $lead->follow_up_date->toDateString());
        $this->assertSame('Confirm the visit', $lead->nextFollowUp->note);

        $this->actingAs($this->agent)->delete("/admin/follow-ups/{$lead->nextFollowUp->id}")->assertRedirect();
        $this->assertNull($lead->fresh()->follow_up_date);
    }

    public function test_other_staff_follow_ups_are_out_of_reach_for_agents(): void
    {
        $followUp = LeadFollowUp::factory()->create();

        $this->actingAs($this->agent)->patch("/admin/follow-ups/{$followUp->id}", ['outcome' => 'x'])->assertForbidden();
        $this->actingAs($this->agent)->post("/admin/leads/{$followUp->lead_id}/follow-ups", ['due_date' => today()->format('Y-m-d')])->assertForbidden();
    }

    public function test_leads_export_to_word_with_custom_fields(): void
    {
        Lead::factory()->create(['name' => 'Word Export Lead', 'extra' => ['location' => 'Khurrianwala & Jhumra Road'], 'message' => "Line one\nLine two"]);

        $response = $this->actingAs($this->admin)->get('/admin/leads/export/docx')->assertOk();
        $this->assertStringContainsString('wordprocessingml', (string) $response->headers->get('Content-Type'));

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($response->getFile()->getPathname()));
        $document = $zip->getFromName('word/document.xml');
        $zip->close();

        $this->assertStringContainsString('Word Export Lead', $document);
        $this->assertStringContainsString('Location (city / area)', $document);
        $this->assertStringContainsString('Khurrianwala &amp; Jhumra Road', $document);
        $this->assertStringContainsString('Line one</w:t><w:br/>', $document);
        $this->assertNotFalse(simplexml_load_string($document), 'document.xml must be valid XML');
    }

    public function test_name_and_phone_cannot_be_switched_off_or_built_in_fields_deleted(): void
    {
        $name = LeadField::query()->where('key', 'name')->firstOrFail();
        $budget = LeadField::query()->where('key', 'budget')->firstOrFail();

        $this->actingAs($this->admin)->patchJson("/admin/toggle/lead-fields/{$name->id}", ['field' => 'is_active', 'value' => false])->assertOk();
        $this->assertTrue($name->fresh()->is_active);

        $this->actingAs($this->admin)->delete("/admin/lead-fields/{$budget->id}")->assertForbidden();

        $this->actingAs($this->admin)->put("/admin/lead-fields/{$budget->id}", ['label' => 'Budget range', 'is_active' => '0', 'is_required' => '0', 'show_in_list' => '0'])
            ->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->get('/admin/leads/create')->assertDontSee('for="f-budget"', false)->assertDontSee('name="budget"', false);
    }
}
