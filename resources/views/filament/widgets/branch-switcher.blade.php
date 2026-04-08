<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Switch Branch
        </x-slot>

        <div class="space-y-2">
            @foreach($this->branches as $branch)
                <button
                    wire:click="switchBranch({{ $branch->id }})"
                    class="w-full text-left px-4 py-2 rounded-lg transition-colors {{ $this->selectedBranch == $branch->id ? 'bg-primary-500 text-white' : 'bg-gray-100 hover:bg-gray-200' }}"
                >
                    {{ $branch->name }}
                </button>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
