<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->unsignedSmallInteger('line_order')->default(1);
            $table->decimal('debit', 19, 4)->default('0.0000');
            $table->decimal('credit', 19, 4)->default('0.0000');
            $table->string('memo', 255)->nullable();
            $table->boolean('is_reconciled')->default(false);
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'account_id'], 'jlines_user_account_idx');
            $table->index(['user_id', 'category_id'], 'jlines_user_category_idx');
            $table->index(['journal_entry_id', 'line_order'], 'jlines_entry_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
    }
};
