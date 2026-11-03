<?php

declare(strict_types=1);

use App\Enums\EntryStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debt_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('debt_id')->constrained('debts')->restrictOnDelete();
            $table->foreignId('source_account_id')->constrained('accounts')->restrictOnDelete();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->restrictOnDelete();
            $table->date('payment_date');
            $table->decimal('total_amount', 19, 4);
            $table->decimal('principal_amount', 19, 4);
            $table->decimal('interest_amount', 19, 4)->default('0.0000');
            $table->decimal('fee_amount', 19, 4)->default('0.0000');
            $table->string('status', 24)->default(EntryStatus::Posted->value);
            $table->string('reference', 120)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique('journal_entry_id', 'debt_payments_jentry_unq');
            $table->index(['user_id', 'debt_id', 'payment_date'], 'debt_payments_user_debt_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
    }
};
