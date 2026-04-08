<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BranchSwitcherWidget extends Widget
{
    protected static string $view = 'filament.widgets.branch-switcher';

    public ?int $selectedBranch = null;

    public function mount(): void
    {
        $this->selectedBranch = Session::get('selected_branch_id', Auth::user()?->branch_id);
    }

    public function switchBranch($branchId): void
    {
        if (Auth::user()?->isAdmin()) {
            Session::put('selected_branch_id', $branchId);
            $this->selectedBranch = $branchId;
            $this->dispatch('branch-switched');
        }
    }

    public function getBranchesProperty()
    {
        return Branch::all();
    }

    public static function canView(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }
}
