<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TodaysOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $query = Order::whereDate('created_at', today());
        
        if (!Auth::user()?->isAdmin()) {
            $query->where('branch_id', Auth::user()->branch_id);
        }

        $count = $query->count();
        $total = $query->sum('grand_total');

        return [
            Stat::make('Today\'s Orders', $count)
                ->description('Total: ₹' . number_format($total, 2))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success')
                ->chart([7, 12, 15, 20, $count])
                ->icon('heroicon-o-shopping-cart'),
        ];
    }
}
