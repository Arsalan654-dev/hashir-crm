<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_team_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->foreignId('sales_team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20)->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['sales_team_id', 'user_id']);

            if (DB::getDriverName() !== 'sqlite') {
                $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_team_members');
    }
};
