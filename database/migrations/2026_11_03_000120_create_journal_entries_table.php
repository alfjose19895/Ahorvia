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
        Schema::create('journal_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('entry_number', 40);
            $table->date('entry_date');
            $table->string('movement_type', 40);
            $table->string('status', 24)->default(EntryStatus::Posted->value);
            $table->char('currency_code', 3)->default('USD');
            $table->string('description', 255);
            $table->string('reference', 120)->nullable();
            $table->string('idempotency_key', 120)->nullable();
            $table->foreignId('reversal_of_entry_id')->nullable()->constrained('journal_entries')->restrictOnDelete();
            $table->foreignId('reversed_by_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();
            $table->string('reversal_reason', 255)->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'entry_number'], 'jentries_user_number_unq');
            $table->unique(['user_id', 'idempotency_key'], 'jentries_user_idemp_unq');
            $table->unique('reversal_of_entry_id', 'jentries_reversal_of_unq');
            $table->index(['user_id', 'entry_date', 'status'], 'jentries_user_date_status_idx');
            $table->index(['user_id', 'movement_type'], 'jentries_user_mtype_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
