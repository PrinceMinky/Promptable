<flux:modal name="{{ $this->promptModalName }}"
        class="min-w-[22rem]"
        x-data="{
        confirmation: $wire.entangle('promptConfirmation'),
        confirmWord: $wire.entangle('promptConfirmWord')
        }"
        x-effect="if (confirmWord) confirmation = ''"
>
    {{-- Question --}}
    <flux:heading size="lg">{{ $this->promptQuestion }}</flux:heading>

    {{-- Prompt Text --}}
    @if ($this->promptText)
        <flux:text class="mt-2">
            {!! nl2br(e($this->promptText)) !!}
        </flux:text>
    @endif

    {{-- Confirmation Input --}}
    @if ($this->promptConfirmWord)
        <flux:input
            x-model="confirmation"
            type="text"
            placeholder="Type `{{ $this->promptConfirmWord }}` to continue..."
            class="mt-2"
            autofocus
            @keydown.enter="confirmWord && confirmation === confirmWord ? $wire.promptConfirm() : null"
        />
    @endif

    <div class="flex justify-end gap-2 mt-4">
        {{-- Close Button --}}
        <flux:button
            variant="ghost"
            x-on:click="$flux.modal('{{ $this->promptModalName }}').close()"
        >
            {{ $this->promptCancelText }}
        </flux:button>

        {{-- Confirm Input --}}
        <flux:button
            variant="danger"
            x-bind:disabled="confirmWord && confirmation !== confirmWord"
            wire:click="promptConfirm()"
        >
            {{ $this->promptConfirmText }}
        </flux:button>
    </div>
</flux:modal>
