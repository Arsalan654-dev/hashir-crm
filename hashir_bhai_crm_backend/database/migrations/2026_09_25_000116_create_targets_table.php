<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('period_type', 20)->default('monthly');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('target_amount', 14, 2)->default(0);
            $table->decimal('target_orders', 10, 0)->nullable();
            $table->decimal('bonus_amount', 12, 2)->default(0);
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};
