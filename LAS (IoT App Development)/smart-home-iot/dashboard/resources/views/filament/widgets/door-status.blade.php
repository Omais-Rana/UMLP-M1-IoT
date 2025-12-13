<x-filament-widgets::widget>
    <x-filament::section>
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="padding: 8px; border-radius: 8px; background-color: {{ $isOpen ? '#fee2e2' : '#d1fae5' }};">
                    <svg style="width: 24px; height: 24px; color: {{ $isOpen ? '#dc2626' : '#16a34a' }};" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        @if ($isOpen)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z">
                            </path>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        @endif
                    </svg>
                </div>
                <div>
                    <h3 style="font-size: 16px; font-weight: 600; margin: 0;">Room Door</h3>
                    <p style="font-size: 14px; color: #6b7280; margin: 4px 0 0 0;">Security Sensor</p>
                </div>
            </div>

            <button wire:click="toggleDoor"
                style="padding: 8px 16px; font-size: 14px; font-weight: 600; color: white; border: none; border-radius: 8px; cursor: pointer; background-color: {{ $isOpen ? '#dc2626' : '#16a34a' }};">
                {{ $isOpen ? 'OPEN' : 'CLOSED' }}
            </button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
