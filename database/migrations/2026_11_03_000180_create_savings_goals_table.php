<?php

declare(strict_types=1);

use App\Enums\SavingsGoalStatus;
use App\Enums\SavingsMode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_goals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('linked_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('name', 150);
            $table->string('mode', 24)->default(SavingsMode::Fixed->value);
            $table->string('status', 24)->default(SavingsGoalStatus::Active->value);
            $table->char('currency_code', 3)->default('USD');
            $table->decimal('target_amount', 19, 4);
            $table->decimal('initial_amount', 19, 4)->default('0.0000');
            $table->date('start_date');
            $table->date('target_date');
            $table->unsignedSmallInteger('duration_months')->default(6);
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'sgoals_user_status_idx');
            $table->index(['user_id', 'target_date'], 'sgoals_user_tdate_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_goals');
    }
};
