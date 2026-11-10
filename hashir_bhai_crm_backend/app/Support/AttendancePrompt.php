<?php

namespace App\Support;

use App\Models\AttendanceRecord;
use App\Models\Setting;
use App\Models\User;

class AttendancePrompt
{
    public static function shouldPrompt(User $user): bool
    {
        if ($user->isSuperAdmin() || ! $user->hasPermission('attendance.mark_own')) {
            return false;
        }

        if (! Setting::boolean('attendance.prompt_enabled', true)) {
            return false;
        }

        $snoozedUntil = session('attendance_prompt_snooze_until');

        if ($snoozedUntil !== null && now()->timestamp < (int) $snoozedUntil) {
            return false;
        }

        $hasRecordToday = AttendanceRecord::query()
            ->where('user_id', $user->id)
            ->whereDate('date', today())
            ->exists();

        if ($hasRecordToday) {
            return false;
        }

        $intervalHours = max(1, (int) Setting::getValue('attendance.prompt_interval_hours', 24));

        if ($user->attendance_prompted_at !== null && $user->attendance_prompted_at->diffInHours(now()) < $intervalHours) {
            return false;
        }

        return true;
    }
}
