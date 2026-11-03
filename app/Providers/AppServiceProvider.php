<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Account;
use App\Models\Attachment;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Debt;
use App\Models\JournalEntry;
use App\Models\RecurringRule;
use App\Models\SavingsGoal;
use App\Models\ScheduledObligation;
use App\Models\User;
use App\Policies\AccountPolicy;
use App\Policies\AttachmentPolicy;
use App\Policies\BudgetPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\DebtPolicy;
use App\Policies\JournalEntryPolicy;
use App\Policies\RecurringRulePolicy;
use App\Policies\SavingsGoalPolicy;
use App\Policies\ScheduledObligationPolicy;
use App\Policies\UserPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Model::shouldBeStrict(! $this->app->isProduction());

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Account::class, AccountPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(JournalEntry::class, JournalEntryPolicy::class);
        Gate::policy(Budget::class, BudgetPolicy::class);
        Gate::policy(Debt::class, DebtPolicy::class);
        Gate::policy(SavingsGoal::class, SavingsGoalPolicy::class);
        Gate::policy(RecurringRule::class, RecurringRulePolicy::class);
        Gate::policy(ScheduledObligation::class, ScheduledObligationPolicy::class);
        Gate::policy(Attachment::class, AttachmentPolicy::class);

        Password::defaults(function (): Password {
            $rule = Password::min(8);

            return $this->app->isProduction()
                ? $rule->letters()->mixedCase()->numbers()
                : $rule;
        });
    }
}
