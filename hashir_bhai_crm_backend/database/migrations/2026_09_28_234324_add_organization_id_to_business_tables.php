<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Business tables that belong to a tenant organization.
     *
     * @var array<int, string>
     */
    private array $tables = [
        'categories', 'products', 'product_media', 'customers', 'contacts',
        'orders', 'order_items', 'invoices', 'invoice_items', 'carts', 'cart_items',
        'leads', 'lead_activities', 'sales', 'sale_items', 'sale_payments',
        'departments', 'designations', 'attendance_records', 'leave_requests', 'holidays',
        'compensations', 'payrolls', 'payroll_items', 'commission_rules', 'targets',
        'chat_contacts', 'chat_messages', 'chat_channels', 'chat_channel_members', 'messages',
        'whatsapp_accounts', 'whatsapp_contacts', 'whatsapp_messages',
        'email_messages', 'gmail_accounts', 'knowledge_articles', 'bot_conversations',
        'todos', 'calendar_events', 'file_manager_items', 'notification_items',
        'activity_logs', 'roles',
    ];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id')->index();

                if (DB::getDriverName() !== 'sqlite') {
                    $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                if (DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign(['organization_id']);
                }

                $table->dropColumn('organization_id');
            });
        }
    }
};
