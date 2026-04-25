<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Supplier;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SupplierPayablesWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $query = Payment::where('type', 'supplier');
        
        if (!Auth::user()?->isAdmin()) {
            $query->where('branch_id', Auth::user()->branch_id);
        }

        // Calculate supplier payables: opening balance + total payments made (negative)
        $suppliers = Supplier::query();
        if (!Auth::user()?->isAdmin()) {
            $suppliers->where('branch_id', Auth::user()->branch_id);
        }
        
        $openingBalance = $suppliers->sum('opening_balance');
        $paymentsMade = $query->sum('amount');
        $total = abs($openingBalance) - $paymentsMade;

        return [
            Stat::make('Supplier Payables', 'Rs. ' . number_format(max(0, $total), 2))
                ->description($total > 0 ? 'Outstanding payments' : 'All paid')
                ->descriptionIcon('heroicon-m-truck')
                ->color($total > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-truck'),
        ];
    }
}
