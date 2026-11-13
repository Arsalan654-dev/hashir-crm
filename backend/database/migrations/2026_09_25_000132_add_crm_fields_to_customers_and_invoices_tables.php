<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('company')->nullable()->after('name');
            $table->string('whatsapp_number', 50)->nullable()->after('phone');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->nullable()->after('city');
            $table->string('status', 20)->default('active')->after('country');
            $table->text('notes')->nullable()->after('status');
            $table->foreignId('assigned_to')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->foreignId('whatsapp_contact_id')->nullable()->after('assigned_to');
            $table->json('tags')->nullable()->after('whatsapp_contact_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('sale_id')->nullable()->after('customer_id')->constrained('sales')->nullOnDelete();
            $table->string('status', 30)->default('draft')->change();
            $table->decimal('paid_amount', 12, 2)->default(0)->after('amount');
            $table->decimal('balance', 12, 2)->default(0)->after('paid_amount');
            $table->string('currency', 10)->default('USD')->after('balance');
            $table->date('due_date')->nullable()->after('invoice_date');
            $table->timestamp('sent_at')->nullable()->after('due_date');
            $table->string('sent_via', 30)->nullable()->after('sent_at');
            $table->text('notes')->nullable()->after('sent_via');
            $table->text('terms')->nullable()->after('notes');
            $table->foreignId('created_by')->nullable()->after('terms')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropColumn(['company', 'whatsapp_number', 'city', 'country', 'status', 'notes', 'whatsapp_contact_id', 'tags']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sale_id');
            $table->dropColumn(['paid_amount', 'balance', 'currency', 'due_date', 'sent_at', 'sent_via', 'notes', 'terms']);
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
