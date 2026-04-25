<?php

namespace App\Filament\Widgets;

use App\Models\Complaint;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Supplier;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class KpiOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $user = Auth::user();

        $ordersTodayQuery = Order::query()->whereDate('created_at', today());
        $upcomingEventsQuery = Order::query()
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereBetween('event_date', [today(), today()->addDays(7)]);
        $pendingDuesQuery = Invoice::query()
            ->where('status', '!=', 'paid')
            ->where('balance_due', '>', 0);
        $openComplaintsQuery = Complaint::query()->whereIn('status', ['open', 'in_progress']);

        $supplierPaymentsQuery = Payment::query()->where('type', 'supplier');
        $suppliersQuery = Supplier::query();

        if (!$user?->isAdmin()) {
            $branchId = $user?->branch_id;
            if ($branchId) {
                $ordersTodayQuery->where('branch_id', $branchId);
                $upcomingEventsQuery->where('branch_id', $branchId);
                $pendingDuesQuery->where('branch_id', $branchId);
                $openComplaintsQuery->where('branch_id', $branchId);
                $supplierPaymentsQuery->where('branch_id', $branchId);
                $suppliersQuery->where('branch_id', $branchId);
            }
        }

        $ordersTodayCount = $ordersTodayQuery->count();
        $ordersTodayTotal = (float) $ordersTodayQuery->sum('grand_total');

        $upcomingEventsCount = $upcomingEventsQuery->count();

        $pendingDuesCount = $pendingDuesQuery->count();
        $pendingDuesTotal = (float) $pendingDuesQuery->sum('balance_due');

        $openComplaintsCount = $openComplaintsQuery->count();

        $openingBalance = (float) $suppliersQuery->sum('opening_balance');
        $paymentsMade = (float) $supplierPaymentsQuery->sum('amount');
        $supplierPayables = max(0, abs($openingBalance) - $paymentsMade);

        return [
            Stat::make("Today's Orders", $ordersTodayCount)
                ->description('Total: Rs. ' . number_format($ordersTodayTotal, 2))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success')
                ->chart([7, 12, 15, 20, $ordersTodayCount])
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('Upcoming Events', $upcomingEventsCount)
                ->description($upcomingEventsCount > 0 ? 'Next 7 days' : 'No upcoming events')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($upcomingEventsCount > 0 ? 'info' : 'gray')
                ->icon('heroicon-o-calendar'),

            Stat::make('Pending Customer Dues', 'Rs. ' . number_format($pendingDuesTotal, 2))
                ->description($pendingDuesCount . ' unpaid invoice' . ($pendingDuesCount !== 1 ? 's' : ''))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Supplier Payables', 'Rs. ' . number_format($supplierPayables, 2))
                ->description($supplierPayables > 0 ? 'Outstanding payments' : 'All paid')
                ->descriptionIcon('heroicon-m-truck')
                ->color($supplierPayables > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-truck'),

            Stat::make('Open Complaints', $openComplaintsCount)
                ->description($openComplaintsCount > 0 ? 'Requires attention' : 'All resolved')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($openComplaintsCount > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}

