<?php

declare(strict_types=1);

use App\Enums\ImportBatchStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('source_filename', 255);
            $table->string('file_path', 255);
            $table->string('status', 24)->default(ImportBatchStatus::Pending->value);
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('summary')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'imp_batches_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};
