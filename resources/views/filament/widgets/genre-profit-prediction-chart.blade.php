<x-filament-widgets::widget xmlns:x-filament="http://www.w3.org/1999/html">
    <x-filament::section>
        <div style="display: flex; flex-direction: column">
            <div >
                <h2 class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">Genre wist voorspelling</h2>
            </div>
            <div style="width: 100%; height: min-content; padding-bottom: 20px">
                <form wire:submit="create">
                    {{ $this->form }}

                    <x-filament::button type="submit" style="margin-top: 10px">
                        Voorspel
                    </x-filament::button>
                </form>
            </div>
            <div style="width: 100%">
                @if(file_exists(public_path('randomtest.png')))
                    <img style="width: 100%" src="{{ asset('randomtest.png') }}">
                @else
                    <p>Wachten op data...</p>
                @endif
            </div>
        </div>
        <x-filament-actions::modals/>
    </x-filament::section>
</x-filament-widgets::widget>
