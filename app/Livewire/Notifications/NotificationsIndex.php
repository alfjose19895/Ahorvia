<?php

declare(strict_types=1);

namespace App\Livewire\Notifications;

use App\Models\User;
use App\Services\Reporting\NotificationCenterService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Notificaciones — Ahorvia')]
class NotificationsIndex extends Component
{
    #[Url(as: 'filtro', history: true)]
    public string $filter = 'all'; // 'all', 'danger', 'warning', 'info'

    public function setFilter(string $level): void
    {
        if (in_array($level, ['all', 'danger', 'warning', 'info'], true)) {
            $this->filter = $level;
        }
    }

    public function render(NotificationCenterService $service): View
    {
        /** @var User $user */
        $user = Auth::user();

        $allNotifications = $service->getNotificationsForUser($user);

        $counts = [
            'all' => count($allNotifications),
            'danger' => count(array_filter($allNotifications, static fn (array $n): bool => $n['level'] === 'danger')),
            'warning' => count(array_filter($allNotifications, static fn (array $n): bool => $n['level'] === 'warning')),
            'info' => count(array_filter($allNotifications, static fn (array $n): bool => $n['level'] === 'info')),
        ];

        $filteredNotifications = $this->filter === 'all'
            ? $allNotifications
            : array_values(array_filter($allNotifications, fn (array $n): bool => $n['level'] === $this->filter));

        return view('livewire.notifications.notifications-index', [
            'notifications' => $filteredNotifications,
            'counts' => $counts,
        ]);
    }
}
