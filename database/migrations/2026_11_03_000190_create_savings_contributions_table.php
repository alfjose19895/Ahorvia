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
        Schema::create('savings_contributions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('savings_goal_id')->constrained('savings_goals')->restrictOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->foreignId('source_account_id')->nullable()->constrained('accounts')->restrictOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->restrictOnDelete();
            $table->string('contribution_type', 24);
            $table->date('contribution_date');
            $table->decimal('amount', 19, 4);
            $table->string('status', 24)->default(EntryStatus::Posted->value);
            $table->string('idempotency_key', 120)->nullable();
            $table->string('reference', 120)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'idempotency_key'], 'sav_contrib_user_idemp_unq');
            $table->index(['user_id', 'savings_goal_id', 'status'], 'sav_contrib_user_goal_st_idx');
            $table->index(['user_id', 'account_id', 'contribution_type', 'status'], 'sav_contrib_reserve_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_contributions');
    }
};
