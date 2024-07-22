<div>
    <form wire:submit="submit">
        <div class="mb-5">
            {{ $this->form }}
        </div>

        <div>
            <x-filament::button type="submit">
                Submit
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</div>
