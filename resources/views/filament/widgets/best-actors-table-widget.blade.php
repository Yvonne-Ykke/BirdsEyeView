<x-filament-widgets::widget xmlns:filament="http://www.w3.org/1999/html">
    <x-filament::section>
        <div class="px-4 sm:px-6 lg:px-8" wire:loading.remove>

            <div class="mt-8 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <livewire:best-actors-table :data="$tableData" key="{{ now() }}"/>
                    </div>
                </div>
            </div>
        </div>
        <div wire:loading class="flex justify-center w-full">
            <div class="mx-auto w-min">
                @include('components.loading-icons.ball-pulse')
            </div>
        </div>
        <x-filament-actions::modals/>
    </x-filament::section>
</x-filament-widgets::widget>
