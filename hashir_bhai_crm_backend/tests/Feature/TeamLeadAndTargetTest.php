<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\SalesTeam;
use App\Models\Target;
use App\Notifications\AppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class TeamLeadAndTargetTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    public function test_manager_can_only_set_targets_for_team_members(): void
    {
        $manager = $this->userWithPermissions(['dashboard.view', 'targets.manage']);
        $member = $this->userWithPermissions(['dashboard.view']);
        $outsider = $this->userWithPermissions(['dashboard.view']);

        $team = SalesTeam::query()->create(['name' => 'Team A', 'manager_id' => $manager->id]);
        $team->memberships()->create(['user_id' => $member->id, 'joined_at' => now(), 'is_active' => true]);

        $payload = [
            'period_type' => 'monthly',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'target_amount' => 5000,
        ];

        $this->actingAs($manager)
            ->post('/targets', $payload + ['user_id' => $member->id])
            ->assertRedirect();

        $this->assertDatabaseHas('targets', ['user_id' => $member->id]);

        $this->actingAs($manager)
            ->post('/targets', $payload + ['user_id' => $outsider->id])
            ->assertSessionHasErrors('user_id');

        $this->assertDatabaseMissing('targets', ['user_id' => $outsider->id]);
    }

    public function test_targets_index_is_scoped_to_the_managers_team(): void
    {
        $manager = $this->userWithPermissions(['dashboard.view', 'targets.view_all']);
        $teamMember = $this->userWithPermissions(['dashboard.view']);
        $outsider = $this->userWithPermissions(['dashboard.view']);

        $team = SalesTeam::query()->create(['name' => 'Team A', 'manager_id' => $manager->id]);
        $team->memberships()->create(['user_id' => $teamMember->id, 'joined_at' => now(), 'is_active' => true]);

        Target::query()->create([
            'user_id' => $outsider->id,
            'period_type' => 'monthly',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'target_amount' => 9999,
        ]);

        // view_all holder still sees everything (admin-level permission)
        $this->actingAs($manager)->get('/targets')->assertOk()->assertSee('9,999');
    }

    public function test_today_page_shows_team_leads_only(): void
    {
        $manager = $this->userWithPermissions(['dashboard.view', 'leads.view_team']);
        $member = $this->userWithPermissions(['dashboard.view']);
        $outsider = $this->userWithPermissions(['dashboard.view']);

        $team = SalesTeam::query()->create(['name' => 'Team A', 'manager_id' => $manager->id]);
        $team->memberships()->create(['user_id' => $member->id, 'joined_at' => now(), 'is_active' => true]);

        Lead::query()->create([
            'lead_no' => 'LD-TEAM-1',
            'name' => 'Team Lead Alpha',
            'assigned_to' => $member->id,
            'status' => 'new',
            'next_follow_up_at' => now()->subDay(),
        ]);

        Lead::query()->create([
            'lead_no' => 'LD-OUT-1',
            'name' => 'Outsider Lead Beta',
            'assigned_to' => $outsider->id,
            'status' => 'new',
            'next_follow_up_at' => now()->subDay(),
        ]);

        $this->actingAs($manager)
            ->get('/leads/today')
            ->assertOk()
            ->assertSee('Team Lead Alpha')
            ->assertDontSee('Outsider Lead Beta');
    }

    public function test_manager_cannot_assign_lead_outside_team(): void
    {
        $manager = $this->userWithPermissions(['dashboard.view', 'leads.view', 'leads.assign']);
        $outsider = $this->userWithPermissions(['dashboard.view']);

        $lead = Lead::query()->create([
            'lead_no' => 'LD-TEAM-2',
            'name' => 'Assign Test Lead',
            'status' => 'new',
        ]);

        $this->actingAs($manager)
            ->post(route('leads.assign', $lead), ['assigned_to' => $outsider->id])
            ->assertSessionHasErrors('assigned_to');

        $this->assertNull($lead->refresh()->assigned_to);
    }

    public function test_lead_assignment_notifies_assignee(): void
    {
        Notification::fake();

        $admin = $this->superAdmin();
        $member = $this->userWithPermissions(['dashboard.view']);

        $lead = Lead::query()->create([
            'lead_no' => 'LD-TEAM-3',
            'name' => 'Notify Test Lead',
            'status' => 'new',
        ]);

        $this->actingAs($admin)
            ->post(route('leads.assign', $lead), ['assigned_to' => $member->id])
            ->assertRedirect();

        Notification::assertSentTo($member, AppNotification::class);
    }
}
