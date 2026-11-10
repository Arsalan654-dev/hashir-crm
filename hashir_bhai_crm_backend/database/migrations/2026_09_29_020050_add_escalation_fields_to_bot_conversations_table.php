<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_conversations', function (Blueprint $table) {
            $table->timestamp('escalated_at')->nullable()->after('ended_at');
            $table->timestamp('resolved_at')->nullable()->after('escalated_at');
            $table->foreignId('handled_by')->nullable()->after('resolved_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bot_conversations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('handled_by');
            $table->dropColumn(['escalated_at', 'resolved_at']);
        });
    }
};
