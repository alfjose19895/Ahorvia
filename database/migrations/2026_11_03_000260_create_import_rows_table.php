<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('import_batch_id')->constrained('import_batches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('raw_payload');
            $table->decimal('parsed_amount', 19, 4)->nullable();
            $table->date('parsed_date')->nullable();
            $table->string('status', 24)->default('pending');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->string('error_message', 255)->nullable();
            $table->timestamps();

            $table->unique(['import_batch_id', 'row_number'], 'imp_rows_batch_rownum_unq');
            $table->index(['user_id', 'status'], 'imp_rows_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_rows');
    }
};
