<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('module', 50)->nullable();
            $table->string('action', 50);
            $table->string('description')->nullable();
            $table->nullableMorphs('subject');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->string('group', 50)->default('general')->after('value');
            $table->string('type', 20)->default('string')->after('group');
            $table->boolean('is_public')->default(false)->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['group', 'type', 'is_public']);
        });

        Schema::dropIfExists('activity_logs');
    }
};
