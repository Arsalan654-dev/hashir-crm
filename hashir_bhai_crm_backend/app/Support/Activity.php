<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class Activity
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public static function log(string $action, ?string $description = null, ?Model $subject = null, array $properties = [], ?string $module = null): ActivityLog
    {
        $user = Auth::user();

        return ActivityLog::create([
            'user_id' => $user?->id,
            'module' => $module ?? ($subject ? class_basename($subject) : 'system'),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties === [] ? null : $properties,
            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 500),
        ]);
    }
}
