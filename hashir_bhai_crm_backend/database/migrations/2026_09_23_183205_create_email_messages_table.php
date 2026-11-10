<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->string('from_name');
            $table->string('from_email')->nullable();
            $table->string('to_email')->nullable();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('teaser')->nullable();
            $table->string('avatar')->nullable();
            $table->string('folder', 30)->default('inbox');
            $table->string('threads')->nullable();
            $table->string('time_label')->nullable();
            $table->string('date_label')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_important')->default(false);
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_messages');
    }
};
