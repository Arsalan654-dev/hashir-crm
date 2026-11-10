<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('business_type', 40)->default('general');
            $table->string('logo')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('timezone', 60)->default('Asia/Dubai');
            $table->string('currency', 10)->default('USD');
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('active');
            $table->string('payment_status', 20)->default('unpaid');
            $table->string('plan_name')->nullable();
            $table->decimal('plan_amount', 12, 2)->default(0);
            $table->string('billing_cycle', 20)->default('monthly');
            $table->date('paid_until')->nullable();
            $table->date('trial_ends_at')->nullable();
            $table->boolean('crm_blocked')->default(false);
            $table->boolean('bot_blocked')->default(false);
            $table->json('settings')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
