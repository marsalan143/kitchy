<?php

namespace App\Filament\Widgets;

use App\Models\Complaint;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class OpenComplaintsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $query = Complaint::whereIn('status', ['open', 'in_progress']);
        
        if (!Auth::user()?->isAdmin()) {
            $query->where('branch_id', Auth::user()->branch_id);
        }

        $count = $query->count();

        return [
            Stat::make('Open Complaints', $count)
                ->description($count > 0 ? 'Requires attention' : 'All resolved')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($count > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
