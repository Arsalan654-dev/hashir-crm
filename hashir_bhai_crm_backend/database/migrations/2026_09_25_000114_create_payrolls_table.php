<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->string('period_label', 20);
            $table->string('currency', 10)->default('USD');
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('commission', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('incentive', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('absent_deduction', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->unsignedSmallInteger('present_days')->default(0);
            $table->unsignedSmallInteger('absent_days')->default(0);
            $table->unsignedSmallInteger('leave_days')->default(0);
            $table->unsignedSmallInteger('working_days')->default(0);
            $table->string('status', 20)->default('draft');
            $table->date('paid_on')->nullable();
            $table->string('payment_method', 30)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'period_year', 'period_month']);
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('earning');
            $table->string('label');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('source')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payrolls');
    }
};
