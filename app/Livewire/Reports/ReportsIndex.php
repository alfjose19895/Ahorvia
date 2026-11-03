<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Data\Reporting\FinancialReportData;
use App\Models\User;
use App\Services\Reporting\FinancialReportingService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Reportes Financieros — Ahorvia')]
class ReportsIndex extends Component
{
    #[Url(as: 'meses', history: true)]
    public int $timeframe = 6;

    public function setTimeframe(int $months): void
    {
        if (in_array($months, [3, 6, 12], true)) {
            $this->timeframe = $months;
        }
    }

    public function render(FinancialReportingService $reportingService): View
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var FinancialReportData $report */
        $report = $reportingService->generateReport($user, $this->timeframe);

        return view('livewire.reports.reports-index', [
            'report' => $report,
        ]);
    }
}
