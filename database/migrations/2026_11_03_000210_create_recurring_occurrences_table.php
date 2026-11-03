<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_occurrences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recurring_rule_id')->constrained('recurring_rules')->cascadeOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->date('scheduled_date');
            $table->decimal('expected_amount', 19, 4);
            $table->string('status', 24)->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['recurring_rule_id', 'scheduled_date'], 'rec_occ_rule_date_unq');
            $table->index(['user_id', 'status', 'scheduled_date'], 'rec_occ_user_st_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_occurrences');
    }
};
