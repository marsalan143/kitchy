<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class UpcomingEventsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $query = Order::whereIn('status', ['pending', 'confirmed'])
            ->whereBetween('event_date', [today(), today()->addDays(7)]);
        
        if (!Auth::user()?->isAdmin()) {
            $query->where('branch_id', Auth::user()->branch_id);
        }

        $count = $query->count();

        return [
            Stat::make('Upcoming Events', $count)
                ->description($count > 0 ? 'Events in next 7 days' : 'No upcoming events')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($count > 0 ? 'info' : 'gray')
                ->icon('heroicon-o-calendar'),
        ];
    }
}
