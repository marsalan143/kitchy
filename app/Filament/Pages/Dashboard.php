<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\KpiOverviewWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            Widgets\AccountWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 1;
    }
}
