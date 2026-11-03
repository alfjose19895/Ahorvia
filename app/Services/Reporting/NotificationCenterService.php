<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Enums\DebtStatus;
use App\Enums\DebtType;
use App\Enums\SavingsGoalStatus;
use App\Models\Debt;
use App\Models\SavingsGoal;
use App\Models\User;
use App\Services\Budget\BudgetMonitorService;
use App\Services\Savings\SavingsPlannerService;
use App\Support\MoneyFormatter;
use Carbon\CarbonImmutable;

class NotificationCenterService
{
    public function __construct(
        private readonly BudgetMonitorService $budgetMonitorService,
        private readonly SavingsPlannerService $savingsPlannerService,
    ) {}

    /**
     * @return list<array{
     *     id: string,
     *     level: 'danger'|'warning'|'info',
     *     category: string,
     *     title: string,
     *     message: string,
     *     action_route: string,
     *     action_label: string,
     *     created_at: string,
     * }>
     */
    public function getNotificationsForUser(User $user, ?CarbonImmutable $referenceDate = null): array
    {
        $now = ($referenceDate ?? CarbonImmutable::now($user->timezone ?: 'UTC'))->startOfDay();
        $notifications = [];

        // 1. Alertas de Presupuesto (80%, 90%, 100%)
        $budgetExecution = $this->budgetMonitorService->evaluateCurrentMonth($user, $now);
        if ($budgetExecution !== null) {
            foreach ($budgetExecution->lines as $line) {
                if ($line['alert_threshold'] >= 100) {
                    $notifications[] = [
                        'id' => 'budget-100-'.$line['category_id'],
                        'level' => 'danger',
                        'category' => 'Presupuestos',
                        'title' => "Límite excedido en {$line['category_name']}",
                        'message' => "Has gastado {$line['spent_amount']} de un límite aprobado de {$line['limit_amount']} ({$line['consumption_percent']}%).",
                        'action_route' => 'budgets.index',
                        'action_label' => 'Ver Presupuestos',
                        'created_at' => $now->toDateString(),
                    ];
                } elseif ($line['alert_threshold'] >= 90) {
                    $notifications[] = [
                        'id' => 'budget-90-'.$line['category_id'],
                        'level' => 'warning',
                        'category' => 'Presupuestos',
                        'title' => "Consumo al 90% en {$line['category_name']}",
                        'message' => "Consumo crítico del {$line['consumption_percent']}%. Restante: {$line['remaining_amount']}.",
                        'action_route' => 'budgets.index',
                        'action_label' => 'Ver Presupuestos',
                        'created_at' => $now->toDateString(),
                    ];
                } elseif ($line['alert_threshold'] >= 80) {
                    $notifications[] = [
                        'id' => 'budget-80-'.$line['category_id'],
                        'level' => 'warning',
                        'category' => 'Presupuestos',
                        'title' => "Alerta preventiva en {$line['category_name']}",
                        'message' => "Has alcanzado el {$line['consumption_percent']}% del límite presupuestado.",
                        'action_route' => 'budgets.index',
                        'action_label' => 'Ver Presupuestos',
                        'created_at' => $now->toDateString(),
                    ];
                }
            }
        }

        // 2. Alertas de Vencimiento de Deudas y Tarjetas
        $activeDebts = Debt::query()
            ->ownedBy($user)
            ->where('status', DebtStatus::Active)
            ->with('liabilityAccount')
            ->get();

        foreach ($activeDebts as $debt) {
            $isCard = $debt->type === DebtType::CreditCard;
            $route = $isCard ? 'cards.index' : 'debts.index';
            $label = $isCard ? 'Pagar Tarjeta' : 'Ver Deuda';

            if ($debt->due_date !== null) {
                $dueDate = CarbonImmutable::parse($debt->due_date)->startOfDay();
                $diffDays = $now->diffInDays($dueDate, false);

                if ($diffDays < 0) {
                    $notifications[] = [
                        'id' => 'debt-overdue-'.$debt->id,
                        'level' => 'danger',
                        'category' => $isCard ? 'Tarjetas' : 'Deudas',
                        'title' => "Vencimiento vencido: {$debt->name}",
                        'message' => "La obligación venció el {$dueDate->format('d/m/Y')}. Registra tu pago para evitar intereses de mora.",
                        'action_route' => $route,
                        'action_label' => $label,
                        'created_at' => $now->toDateString(),
                    ];
                } elseif ($diffDays <= 7) {
                    $notifications[] = [
                        'id' => 'debt-due-'.$debt->id,
                        'level' => 'warning',
                        'category' => $isCard ? 'Tarjetas' : 'Deudas',
                        'title' => "Próximo vencimiento: {$debt->name}",
                        'message' => "Vence en {$diffDays} ".($diffDays === 1 ? 'día' : 'días')." ({$dueDate->format('d/m/Y')}).",
                        'action_route' => $route,
                        'action_label' => $label,
                        'created_at' => $now->toDateString(),
                    ];
                }
            } elseif ($debt->payment_due_day !== null) {
                $dueDay = (int) $debt->payment_due_day;
                $currentDay = $now->day;
                $daysUntil = $dueDay - $currentDay;

                if ($daysUntil >= 0 && $daysUntil <= 5) {
                    $notifications[] = [
                        'id' => 'debt-monthly-due-'.$debt->id,
                        'level' => 'warning',
                        'category' => $isCard ? 'Tarjetas' : 'Deudas',
                        'title' => "Día de pago próximo: {$debt->name}",
                        'message' => "Tu fecha mensual de pago es el día {$dueDay} (en {$daysUntil} ".($daysUntil === 1 ? 'día' : 'días').').',
                        'action_route' => $route,
                        'action_label' => $label,
                        'created_at' => $now->toDateString(),
                    ];
                }
            }
        }

        // 3. Alertas del Planificador Inteligente de Ahorro
        $activeGoals = SavingsGoal::query()
            ->ownedBy($user)
            ->where('status', SavingsGoalStatus::Active)
            ->get();

        foreach ($activeGoals as $goal) {
            $plan = $this->savingsPlannerService->analyzeGoal($goal, $now);

            if ($plan->requiresAdjustment) {
                $notifications[] = [
                    'id' => 'goal-adjustment-'.$goal->id,
                    'level' => 'warning',
                    'category' => 'Metas de Ahorro',
                    'title' => "Cuota recalculada: {$goal->name}",
                    'message' => 'Se omitieron aportes previos. La cuota recomendada aumentó a '.MoneyFormatter::format($plan->recommendedMonthlyQuota, $goal->currency_code).' para cumplir el objetivo.',
                    'action_route' => 'savings.index',
                    'action_label' => 'Ver Meta de Ahorro',
                    'created_at' => $now->toDateString(),
                ];
            }
        }

        // 4. Recomendación de Seguridad: 2FA TOTP
        if (! $user->hasConfirmedTwoFactor()) {
            $notifications[] = [
                'id' => 'sec-2fa',
                'level' => 'info',
                'category' => 'Seguridad',
                'title' => 'Protege tus finanzas con 2FA TOTP',
                'message' => 'Tu cuenta no tiene activada la autenticación en dos pasos. Actívala para proteger tu información financiera personal.',
                'action_route' => 'settings.index',
                'action_label' => 'Configurar 2FA',
                'created_at' => $now->toDateString(),
            ];
        }

        return $notifications;
    }
}
