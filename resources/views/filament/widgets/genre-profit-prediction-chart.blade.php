<x-filament-widgets::widget xmlns:x-filament="http://www.w3.org/1999/html">
    <x-filament::section>
        <div style="display: flex; flex-direction: column">
            <div>
                <h2 class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">Genre wist
                    voorspelling</h2>
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
                @php($img = 'randomtest.png')
                @if(\Illuminate\Support\Facades\Storage::exists('public/r/randomtest.png'))
                    <livewire:genre-prediction-image :imageName="$img" key="{{ now() }}" />
                @else
                    <p>Wachten op data...</p>
                @endif
            </div>
        </div>
        <x-filament-actions::modals/>
    </x-filament::section>
</x-filament-widgets::widget>
