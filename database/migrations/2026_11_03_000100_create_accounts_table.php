<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('type', 32);
            $table->string('ledger_nature', 24);
            $table->char('currency_code', 3)->default('USD');
            $table->string('institution_name', 150)->nullable();
            $table->string('account_number_last4', 8)->nullable();
            $table->decimal('credit_limit', 19, 4)->nullable();
            $table->unsignedTinyInteger('billing_cutoff_day')->nullable();
            $table->unsignedTinyInteger('payment_due_day')->nullable();
            $table->decimal('interest_rate_annual', 19, 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->boolean('include_in_net_worth')->default(true);
            $table->timestamp('last_reconciled_at')->nullable();
            $table->decimal('last_reconciled_balance', 19, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_system', 'is_active'], 'accounts_user_sys_active_idx');
            $table->index(['user_id', 'type'], 'accounts_user_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
