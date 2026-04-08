<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Switch Branch
            </x-slot>

            <x-slot name="description">
                Select a branch to filter data across the admin panel. This only affects your view and does not change your assigned branch.
            </x-slot>

            <div class="space-y-3">
                @foreach($this->branches as $branch)
                    <button
                        wire:click="switchBranch({{ $branch->id }})"
                        class="w-full text-left px-4 py-3 rounded-lg transition-colors border-2 {{ $this->selectedBranch == $branch->id ? 'bg-primary-500 text-white border-primary-600' : 'bg-white hover:bg-gray-50 border-gray-200 text-gray-900' }}"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold">{{ $branch->name }}</div>
                                @if($branch->phone)
                                    <div class="text-sm {{ $this->selectedBranch == $branch->id ? 'text-primary-100' : 'text-gray-500' }}">
                                        {{ $branch->phone }}
                                    </div>
                                @endif
                            </div>
                            @if($this->selectedBranch == $branch->id)
                                <x-heroicon-o-check-circle class="w-6 h-6" />
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
