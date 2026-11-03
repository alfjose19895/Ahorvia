<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('attachable_type', 120);
            $table->unsignedBigInteger('attachable_id');
            $table->string('disk', 32)->default('local');
            $table->string('file_path', 255);
            $table->string('original_name', 255);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size_bytes');
            $table->char('checksum_sha256', 64)->nullable();
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id'], 'attachments_morph_idx');
            $table->index(['user_id', 'created_at'], 'attachments_user_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
