<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone_number')->nullable();
            $table->string('phone_number_id')->nullable();
            $table->string('business_account_id')->nullable();
            $table->string('waba_id')->nullable();
            $table->text('access_token')->nullable();
            $table->string('verify_token')->nullable();
            $table->string('app_id')->nullable();
            $table->text('app_secret')->nullable();
            $table->string('api_version', 10)->default('v21.0');
            $table->string('status', 20)->default('inactive');
            $table->boolean('is_default')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_error')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('whatsapp_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('wa_id', 50);
            $table->string('phone', 50)->nullable();
            $table->string('name')->nullable();
            $table->string('profile_name')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->string('source', 30)->default('whatsapp');
            $table->boolean('is_blocked')->default(false);
            $table->boolean('is_business')->default(false);
            $table->timestamp('last_message_at')->nullable();
            $table->foreignId('lead_id')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('chat_channel_id')->nullable();
            $table->json('tags')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['whatsapp_account_id', 'wa_id']);
        });

        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('whatsapp_contact_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('chat_channel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('wa_message_id')->nullable()->index();
            $table->string('direction', 10)->default('in');
            $table->string('type', 20)->default('text');
            $table->text('body')->nullable();
            $table->string('caption')->nullable();
            $table->string('media_id')->nullable();
            $table->string('media_url')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('filename')->nullable();
            $table->string('status', 20)->default('received');
            $table->string('error_message')->nullable();
            $table->json('raw')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_contacts');
        Schema::dropIfExists('whatsapp_accounts');
    }
};
