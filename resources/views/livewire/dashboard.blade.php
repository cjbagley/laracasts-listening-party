<?php

use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';

    public $start_time;

    public function createListeningParty() {}

    public function with(): array
    {
        return [
            'listening_parties' => \App\Models\ListeningParty::all(),
        ];
    }
}; ?>

<div>
    Hello, World!
</div>
