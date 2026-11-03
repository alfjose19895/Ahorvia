<?php

declare(strict_types=1);

use App\Enums\ObligationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_obligations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('debt_id')->nullable()->constrained('debts')->nullOnDelete();
            $table->foreignId('recurring_rule_id')->nullable()->constrained('recurring_rules')->nullOnDelete();
            $table->string('title', 150);
            $table->decimal('amount', 19, 4);
            $table->char('currency_code', 3)->default('USD');
            $table->date('due_date');
            $table->string('status', 24)->default(ObligationStatus::Pending->value);
            $table->boolean('is_included_in_budget')->default(false);
            $table->foreignId('paid_journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'due_date'], 'sched_ob_user_st_due_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_obligations');
    }
};
