<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\AppNotification;
use App\Support\AttendancePrompt;
use App\Support\Notifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class AttendancePromptTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    public function test_prompt_appears_for_user_without_attendance_today(): void
    {
        $user = $this->userWithPermissions(['dashboard.view', 'attendance.mark_own']);

        $this->assertTrue(AttendancePrompt::shouldPrompt($user));

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('attendance-prompt-modal');
    }

    public function test_user_without_permission_is_never_prompted(): void
    {
        $user = $this->userWithPermissions(['dashboard.view']);

        $this->assertFalse(AttendancePrompt::shouldPrompt($user));
    }

    public function test_prompt_present_creates_attendance_and_notifies_manager(): void
    {
        Notification::fake();

        $manager = $this->userWithPermissions(['dashboard.view', 'attendance.view_all']);
        $user = $this->userWithPermissions(
            ['dashboard.view', 'attendance.mark_own'],
            ['reports_to' => $manager->id],
        );

        $this->actingAs($user)->post('/attendance/prompt', ['action' => 'present'])->assertRedirect();

        $record = AttendanceRecord::query()
            ->where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        $this->assertNotNull($record);
        $this->assertNotNull($record->check_in);
        $this->assertNotNull($user->fresh()->attendance_prompted_at);
        $this->assertFalse(AttendancePrompt::shouldPrompt($user->fresh()));

        Notification::assertSentTo($manager, AppNotification::class);
    }

    public function test_prompt_leave_creates_pending_leave_request_and_notifies(): void
    {
        Notification::fake();

        $manager = $this->userWithPermissions(['dashboard.view', 'leaves.approve']);
        $user = $this->userWithPermissions(
            ['dashboard.view', 'attendance.mark_own'],
            ['reports_to' => $manager->id],
        );

        $this->actingAs($user)->post('/attendance/prompt', ['action' => 'leave'])->assertRedirect();

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->assertTrue(
            LeaveRequest::query()
                ->where('user_id', $user->id)
                ->whereDate('start_date', today())
                ->exists(),
        );

        Notification::assertSentTo($manager, AppNotification::class);
    }

    public function test_prompt_later_snoozes_the_modal(): void
    {
        $user = $this->userWithPermissions(['dashboard.view', 'attendance.mark_own']);

        $this->actingAs($user)
            ->post('/attendance/prompt', ['action' => 'later'])
            ->assertRedirect()
            ->assertSessionHas('attendance_prompt_snooze_until');
    }

    public function test_prompt_disappears_after_attendance_is_marked(): void
    {
        $user = $this->userWithPermissions(['dashboard.view', 'attendance.mark_own']);

        AttendanceRecord::query()->create([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'check_in' => '09:00:00',
            'status' => 'present',
            'marked_by' => $user->id,
        ]);

        $this->assertFalse(AttendancePrompt::shouldPrompt($user->fresh()));

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('attendance-prompt-modal');
    }

    public function test_manager_without_own_attendance_permission_does_not_get_prompted(): void
    {
        $manager = $this->userWithPermissions(['dashboard.view', 'attendance.view_all']);

        $this->assertFalse(AttendancePrompt::shouldPrompt($manager));
    }

    public function test_notifier_escalates_to_reporting_manager(): void
    {
        Notification::fake();

        $manager = $this->userWithPermissions(['dashboard.view']);
        $user = $this->userWithPermissions(['dashboard.view'], ['reports_to' => $manager->id]);

        Notifier::send(Notifier::managersOf($user), 'Test', 'Body');

        Notification::assertSentTo($manager, AppNotification::class);

        $this->assertInstanceOf(User::class, $manager);
    }
}
