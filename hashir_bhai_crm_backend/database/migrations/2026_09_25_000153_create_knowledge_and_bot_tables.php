<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->default('general');
            $table->string('type', 30)->default('faq');
            $table->longText('content');
            $table->json('tags')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('share_with_bot')->default(true);
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('bot_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->index();
            $table->foreignId('whatsapp_contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('status', 30)->default('active');
            $table->string('language', 10)->nullable();
            $table->longText('summary')->nullable();
            $table->json('transcript')->nullable();
            $table->json('order_data')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_conversations');
        Schema::dropIfExists('knowledge_articles');
    }
};
