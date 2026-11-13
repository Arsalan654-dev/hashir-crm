<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasPermissions;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable([
    'name', 'email', 'password', 'username', 'phone', 'avatar', 'designation', 'bio',
    'address', 'city', 'country', 'role', 'role_id', 'department_id', 'designation_id',
    'reports_to', 'employee_code', 'employment_type', 'joining_date', 'date_of_birth',
    'gender', 'marital_status', 'national_id', 'emergency_contact_name', 'emergency_contact_phone',
    'basic_salary', 'salary_currency', 'commission_rate', 'hourly_rate', 'bank_name',
    'bank_account_number', 'bank_iban', 'whatsapp_number', 'last_login_at', 'last_seen_at',
    'attendance_prompted_at', 'is_super_admin', 'preferences', 'admin_note', 'is_active',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use BelongsToOrganization, HasFactory, HasPermissions, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_super_admin' => 'boolean',
            'joining_date' => 'date',
            'date_of_birth' => 'date',
            'last_login_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'attendance_prompted_at' => 'datetime',
            'basic_salary' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
            'preferences' => 'array',
        ];
    }

    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class);
    }

    public function belongsToOrganization(): bool
    {
        return $this->organization_id !== null && ! $this->isSuperAdmin();
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designationRecord(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reports_to');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'reports_to');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'salesperson_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function compensations(): HasMany
    {
        return $this->hasMany(Compensation::class);
    }

    public function targets(): HasMany
    {
        return $this->hasMany(Target::class);
    }

    public function managedTeams(): HasMany
    {
        return $this->hasMany(SalesTeam::class, 'manager_id');
    }

    public function teamMemberships(): HasMany
    {
        return $this->hasMany(SalesTeamMember::class);
    }

    public function salesTeams(): BelongsToMany
    {
        return $this->belongsToMany(SalesTeam::class, 'sales_team_members')
            ->withPivot(['role', 'joined_at', 'is_active', 'organization_id'])
            ->withTimestamps();
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(UserAllowance::class);
    }

    public function managesAnyTeam(): bool
    {
        return $this->managedTeams()->exists();
    }

    /**
     * Own id plus every active member of teams this user manages.
     *
     * @return Collection<int, int>
     */
    public function teamMemberIds(): Collection
    {
        $ids = collect([$this->id]);

        $teamIds = $this->managedTeams()->pluck('id');

        if ($teamIds->isNotEmpty()) {
            $ids = $ids->merge(
                SalesTeamMember::query()
                    ->whereIn('sales_team_id', $teamIds)
                    ->where('is_active', true)
                    ->pluck('user_id'),
            );
        }

        return $ids->unique()->values();
    }

    public function chatChannels(): HasMany
    {
        return $this->hasMany(ChatChannelMember::class);
    }

    public function gmailAccounts(): HasMany
    {
        return $this->hasMany(GmailAccount::class);
    }

    public function avatarUrl(): string
    {
        if (filled($this->avatar)) {
            return str_starts_with((string) $this->avatar, 'http') || str_starts_with((string) $this->avatar, 'assets/')
                ? asset($this->avatar)
                : asset('storage/'.$this->avatar);
        }

        return asset('assets/images/users/avatar-1.jpg');
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];

        return strtoupper(mb_substr($parts[0] ?? 'U', 0, 1).mb_substr($parts[1] ?? '', 0, 1));
    }

    public function preference(string $key, mixed $default = null): mixed
    {
        return data_get($this->preferences, $key, $default);
    }
}
