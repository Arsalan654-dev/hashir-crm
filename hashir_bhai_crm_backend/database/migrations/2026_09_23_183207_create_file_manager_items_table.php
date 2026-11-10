<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_manager_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 30)->default('file');
            $table->string('size')->nullable();
            $table->string('date_label')->nullable();
            $table->string('icon')->nullable();
            $table->string('icon_class', 50)->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('file_manager_items')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_manager_items');
    }
};
