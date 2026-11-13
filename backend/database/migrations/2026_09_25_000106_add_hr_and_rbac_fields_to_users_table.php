<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('role_id')->constrained('departments')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->after('department_id')->constrained('designations')->nullOnDelete();
            $table->foreignId('reports_to')->nullable()->after('designation_id')->constrained('users')->nullOnDelete();

            $table->string('employee_code', 50)->nullable()->unique()->after('reports_to');
            $table->string('employment_type', 30)->default('full-time')->after('employee_code');
            $table->date('joining_date')->nullable()->after('employment_type');
            $table->date('date_of_birth')->nullable()->after('joining_date');
            $table->string('gender', 20)->nullable()->after('date_of_birth');
            $table->string('marital_status', 20)->nullable()->after('gender');
            $table->string('national_id', 60)->nullable()->after('marital_status');
            $table->string('emergency_contact_name')->nullable()->after('national_id');
            $table->string('emergency_contact_phone', 50)->nullable()->after('emergency_contact_name');

            $table->decimal('basic_salary', 12, 2)->default(0)->after('emergency_contact_phone');
            $table->string('salary_currency', 10)->default('USD')->after('basic_salary');
            $table->decimal('commission_rate', 5, 2)->default(0)->after('salary_currency');
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('commission_rate');
            $table->string('bank_name')->nullable()->after('hourly_rate');
            $table->string('bank_account_number', 60)->nullable()->after('bank_name');
            $table->string('bank_iban', 60)->nullable()->after('bank_account_number');

            $table->string('whatsapp_number', 50)->nullable()->after('bank_iban');
            $table->timestamp('last_login_at')->nullable()->after('whatsapp_number');
            $table->timestamp('last_seen_at')->nullable()->after('last_login_at');
            $table->boolean('is_super_admin')->default(false)->after('last_seen_at');
            $table->json('preferences')->nullable()->after('is_super_admin');
            $table->text('admin_note')->nullable()->after('preferences');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('designation_id');
            $table->dropConstrainedForeignId('reports_to');
            $table->dropColumn([
                'employee_code', 'employment_type', 'joining_date', 'date_of_birth', 'gender',
                'marital_status', 'national_id', 'emergency_contact_name', 'emergency_contact_phone',
                'basic_salary', 'salary_currency', 'commission_rate', 'hourly_rate', 'bank_name',
                'bank_account_number', 'bank_iban', 'whatsapp_number', 'last_login_at', 'last_seen_at',
                'is_super_admin', 'preferences', 'admin_note',
            ]);
        });
    }
};
