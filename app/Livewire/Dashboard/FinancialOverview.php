<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Models\User;
use App\Services\Finance\DashboardSummaryService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard Financiero — Ahorvia')]
class FinancialOverview extends Component
{
    public function render(DashboardSummaryService $summaryService): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('livewire.dashboard.financial-overview', [
            'metrics' => $summaryService->summarizeForUser($user),
            'user' => $user,
        ]);
    }
}
