<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gmail_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('display_name')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('scope')->nullable();
            $table->string('history_id')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_shared')->default(true);
            $table->boolean('sync_enabled')->default(true);
            $table->text('last_error')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::table('email_messages', function (Blueprint $table) {
            $table->foreignId('gmail_account_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->string('provider', 20)->default('local')->after('gmail_account_id');
            $table->string('gmail_message_id')->nullable()->after('provider');
            $table->string('thread_id')->nullable()->after('gmail_message_id');
            $table->json('to_recipients')->nullable()->after('to_email');
            $table->json('cc_recipients')->nullable()->after('to_recipients');
            $table->json('bcc_recipients')->nullable()->after('cc_recipients');
            $table->longText('html_body')->nullable()->after('body');
            $table->string('snippet')->nullable()->after('html_body');
            $table->boolean('has_attachments')->default(false)->after('snippet');
            $table->json('attachments')->nullable()->after('has_attachments');
            $table->json('labels')->nullable()->after('attachments');
            $table->text('raw_headers')->nullable()->after('labels');
        });
    }

    public function down(): void
    {
        Schema::table('email_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gmail_account_id');
            $table->dropColumn([
                'provider', 'gmail_message_id', 'thread_id', 'to_recipients', 'cc_recipients',
                'bcc_recipients', 'html_body', 'snippet', 'has_attachments', 'attachments',
                'labels', 'raw_headers',
            ]);
        });

        Schema::dropIfExists('gmail_accounts');
    }
};
