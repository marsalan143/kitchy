<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PendingDuesWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $query = Invoice::where('status', '!=', 'paid')
            ->where('balance_due', '>', 0);
        
        if (!Auth::user()?->isAdmin()) {
            $query->where('branch_id', Auth::user()->branch_id);
        }

        $total = $query->sum('balance_due');
        $count = $query->count();

        return [
            Stat::make('Pending Customer Dues', '₹' . number_format($total, 2))
                ->description($count . ' unpaid invoice' . ($count !== 1 ? 's' : ''))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
