<?php

declare(strict_types=1);

use App\Enums\DebtStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('liability_account_id')->constrained('accounts')->restrictOnDelete();
            $table->foreignId('budget_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name', 150);
            $table->string('type', 32);
            $table->string('status', 24)->default(DebtStatus::Active->value);
            $table->string('creditor_name', 150)->nullable();
            $table->char('currency_code', 3)->default('USD');
            $table->decimal('original_principal', 19, 4);
            $table->decimal('credit_limit', 19, 4)->nullable();
            $table->decimal('interest_rate_annual', 19, 4)->default('0.0000');
            $table->decimal('minimum_payment', 19, 4)->default('0.0000');
            $table->date('start_date');
            $table->date('due_date')->nullable();
            $table->unsignedTinyInteger('payment_due_day')->nullable();
            $table->unsignedTinyInteger('cutoff_day')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'debts_user_status_idx');
            $table->index(['user_id', 'due_date'], 'debts_user_duedate_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
