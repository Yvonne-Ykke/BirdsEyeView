<x-filament-widgets::widget xmlns:filament="http://www.w3.org/1999/html">
    <x-filament::section>
        <div class="px-4 sm:px-6 lg:px-8" wire:loading.remove>
            <div class="sm:flex sm:items-center justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold leading-6 text-gray-900"> {{ $title  }}</h1>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <div class="relative inline-block text-left" x-data="{ open: false }">
                        <div>
                            <button type="button"
                                    @click="open = !open"
                                    @click.outside="open = false"
                                    class="inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50"
                                    id="menu-button" aria-expanded="true" aria-haspopup="true">
                                <span class="sr-only">Open Filters</span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                     class="w-5 h-5 text-gray-400">
                                    <path fill-rule="evenodd"
                                          d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 0 1 .628.74v2.288a2.25 2.25 0 0 1-.659 1.59l-4.682 4.683a2.25 2.25 0 0 0-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 0 1 8 18.25v-5.757a2.25 2.25 0 0 0-.659-1.591L2.659 6.22A2.25 2.25 0 0 1 2 4.629V2.34a.75.75 0 0 1 .628-.74Z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>

                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"

                            class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                            role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                            <div class="py-1" role="none">
                                <form wire:submit="create">
                                    <div class="flex flex-col p-5">
                                        <div>{{ $this->form }}</div>
                                        <div class="flex justify-end">
                                            <x-filament::button type="submit" class="mt-3">
                                                Filter
                                            </x-filament::button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="bg-white px-2 text-sm text-gray-500"></span>
                </div>
            </div>

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
