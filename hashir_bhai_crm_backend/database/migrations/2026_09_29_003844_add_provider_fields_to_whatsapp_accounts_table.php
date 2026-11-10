<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            $table->string('provider', 30)->default('meta_cloud')->after('name');
            $table->text('api_key')->nullable()->after('access_token');
            $table->string('base_url')->nullable()->after('api_key');
            $table->boolean('bot_enabled')->default(false)->after('is_default');
            $table->string('webhook_secret')->nullable()->after('bot_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            $table->dropColumn(['provider', 'api_key', 'base_url', 'bot_enabled', 'webhook_secret']);
        });
    }
};
