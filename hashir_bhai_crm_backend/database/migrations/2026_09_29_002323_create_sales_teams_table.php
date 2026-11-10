<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('color', 30)->default('primary');
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            if (DB::getDriverName() !== 'sqlite') {
                $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_teams');
    }
};
