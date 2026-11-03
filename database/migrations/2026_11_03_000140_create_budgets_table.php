<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->char('currency_code', 3)->default('USD');
            $table->string('name', 120);
            $table->decimal('total_limit', 19, 4)->default('0.0000');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'year', 'month'], 'budgets_user_year_month_unq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
