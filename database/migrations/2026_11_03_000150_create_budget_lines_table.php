<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->decimal('limit_amount', 19, 4);
            $table->timestamp('alert_80_notified_at')->nullable();
            $table->timestamp('alert_90_notified_at')->nullable();
            $table->timestamp('alert_100_notified_at')->nullable();
            $table->timestamps();

            $table->unique(['budget_id', 'category_id'], 'blines_budget_cat_unq');
            $table->index(['user_id', 'category_id'], 'blines_user_cat_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_lines');
    }
};
