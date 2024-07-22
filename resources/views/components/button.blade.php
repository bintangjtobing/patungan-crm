<x-filament::button
    type="{{ $type }}"
    size="{{ $size }}"
    wire:submit="{{ $wireSubmit }}"
>
    {{ $slot }}
</x-filament::button>
