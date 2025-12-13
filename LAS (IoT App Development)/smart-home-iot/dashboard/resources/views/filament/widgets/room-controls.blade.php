<x-filament-widgets::widget>
    <x-filament::section>
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="padding: 8px; border-radius: 8px; background-color: {{ $isOn ? '#fef3c7' : '#f3f4f6' }};">
                    <svg style="width: 24px; height: 24px; color: {{ $isOn ? '#f59e0b' : '#6b7280' }};" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                        </path>
                    </svg>
                </div>
                <div>
                    <h3 style="font-size: 16px; font-weight: 600; margin: 0;">Main Light</h3>
                    <p style="font-size: 14px; color: #6b7280; margin: 4px 0 0 0;">Living Room</p>
                </div>
            </div>

            <button wire:click="toggleLight"
                style="padding: 8px 16px; font-size: 14px; font-weight: 600; color: white; border: none; border-radius: 8px; cursor: pointer; background-color: {{ $isOn ? '#16a34a' : '#6b7280' }};">
                {{ $isOn ? 'TURN OFF' : 'TURN ON' }}
            </button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
