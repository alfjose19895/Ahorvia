<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name', 120);
            $table->string('slug', 120);
            $table->string('type', 24);
            $table->string('color', 16)->default('#10B981');
            $table->string('icon', 48)->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'type', 'slug'], 'categories_user_type_slug_unq');
            $table->index(['user_id', 'type', 'is_active'], 'categories_user_type_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
