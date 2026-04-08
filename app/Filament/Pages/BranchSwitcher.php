<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BranchSwitcher extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static string $view = 'filament.pages.branch-switcher';

    protected static ?string $title = 'Switch Branch';

    protected static bool $shouldRegisterNavigation = false;

    public ?int $selectedBranch = null;

    public function mount(): void
    {
        if (!Auth::user()?->isAdmin()) {
            abort(403, 'Only admins can switch branches');
        }

        $this->selectedBranch = Session::get('selected_branch_id', Auth::user()?->branch_id);
    }

    public function switchBranch($branchId): void
    {
        if (Auth::user()?->isAdmin()) {
            Session::put('selected_branch_id', $branchId);
            $this->selectedBranch = $branchId;
            $this->dispatch('branch-switched');
            
            $this->redirect(route('filament.admin.pages.dashboard'));
        }
    }

    public function getBranchesProperty()
    {
        return Branch::all();
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }
}
