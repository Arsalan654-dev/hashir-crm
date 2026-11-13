<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\SalesTeam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class SalesTeamTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    public function test_super_admin_can_create_team_with_manager_and_members(): void
    {
        $admin = $this->superAdmin();
        $manager = $this->userWithPermissions(['dashboard.view']);
        $member = $this->userWithPermissions(['dashboard.view']);

        $this->actingAs($admin)->post('/sales-teams', [
            'name' => 'Team A',
            'manager_id' => $manager->id,
            'members' => [$member->id],
        ])->assertRedirect();

        $team = SalesTeam::query()->where('name', 'Team A')->first();

        $this->assertNotNull($team);
        $this->assertSame($manager->id, $team->manager_id);
        $this->assertDatabaseHas('sales_team_members', [
            'sales_team_id' => $team->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_manager_sees_own_team_sales_only(): void
    {
        $manager = $this->userWithPermissions(['dashboard.view', 'sales.view', 'teams.view', 'teams.manage']);
        $teamMember = $this->userWithPermissions(['dashboard.view']);
        $outsider = $this->userWithPermissions(['dashboard.view']);

        $team = SalesTeam::query()->create(['name' => 'Team A', 'manager_id' => $manager->id]);
        $team->memberships()->create(['user_id' => $teamMember->id, 'joined_at' => now(), 'is_active' => true]);

        Sale::query()->create([
            'sale_no' => 'SL-TEST-1',
            'salesperson_id' => $teamMember->id,
            'customer_name' => 'Team Customer',
            'total' => 100,
            'sale_date' => today()->toDateString(),
            'status' => 'confirmed',
        ]);

        Sale::query()->create([
            'sale_no' => 'SL-TEST-2',
            'salesperson_id' => $outsider->id,
            'customer_name' => 'Outsider Customer',
            'total' => 200,
            'sale_date' => today()->toDateString(),
            'status' => 'confirmed',
        ]);

        $visible = Sale::query()->forUser($manager)->pluck('salesperson_id');

        $this->assertTrue($visible->contains($teamMember->id));
        $this->assertFalse($visible->contains($outsider->id));
    }

    public function test_team_manager_cannot_manage_other_teams(): void
    {
        $manager = $this->userWithPermissions(['dashboard.view', 'teams.view', 'teams.manage_members']);
        $member = $this->userWithPermissions(['dashboard.view']);

        $ownTeam = SalesTeam::query()->create(['name' => 'Team A', 'manager_id' => $manager->id]);
        $otherTeam = SalesTeam::query()->create(['name' => 'Team B']);

        $this->actingAs($manager)
            ->post(route('teams.members.store', $otherTeam), ['user_ids' => [$member->id]])
            ->assertSessionHasErrors('team');

        $this->assertDatabaseMissing('sales_team_members', [
            'sales_team_id' => $otherTeam->id,
            'user_id' => $member->id,
        ]);

        $this->actingAs($manager)
            ->post(route('teams.members.store', $ownTeam), ['user_ids' => [$member->id]])
            ->assertRedirect();

        $this->assertDatabaseHas('sales_team_members', [
            'sales_team_id' => $ownTeam->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_teams_index_is_scoped_for_managers(): void
    {
        $manager = $this->userWithPermissions(['dashboard.view', 'teams.view']);

        SalesTeam::query()->create(['name' => 'Alpha Team', 'manager_id' => $manager->id]);
        SalesTeam::query()->create(['name' => 'Beta Team']);

        $this->actingAs($manager)
            ->get('/sales-teams')
            ->assertOk()
            ->assertSee('Alpha Team')
            ->assertDontSee('Beta Team');
    }

    public function test_sales_person_without_team_sees_only_own_sales(): void
    {
        $salesperson = $this->userWithPermissions(['dashboard.view', 'sales.view']);
        $colleague = $this->userWithPermissions(['dashboard.view']);

        Sale::query()->create([
            'sale_no' => 'SL-TEST-3',
            'salesperson_id' => $salesperson->id,
            'customer_name' => 'Mine',
            'total' => 50,
            'sale_date' => today()->toDateString(),
        ]);

        Sale::query()->create([
            'sale_no' => 'SL-TEST-4',
            'salesperson_id' => $colleague->id,
            'customer_name' => 'Not Mine',
            'total' => 75,
            'sale_date' => today()->toDateString(),
        ]);

        $visible = Sale::query()->forUser($salesperson)->pluck('salesperson_id');

        $this->assertTrue($visible->contains($salesperson->id));
        $this->assertFalse($visible->contains($colleague->id));
    }
}
