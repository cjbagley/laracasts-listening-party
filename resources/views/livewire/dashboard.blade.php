<?php

use Livewire\Volt\Component;
use App\Models\ListeningParty;
use App\Models\Episode;
use Livewire\Attributes\Validate;

new class extends Component {
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|url')]
    public string $mediaUrl = '';

    #[Validate('required')]
    public $startTime;

    public function createListeningParty()
    {
        $this->validate();

        $episode = Episode::create([
            'media_url' => $this->mediaUrl,
        ]);

        $listening_party = ListeningParty::create([
            'episode_id' => $episode->id,
            'name' => $this->name,
            'start_time' => $this->startTime,
        ]);

        return redirect()->route('parties.show', $listening_party);
    }

    public function with(): array
    {
        return [
            'listening_parties' => ListeningParty::all(),
        ];
    }
}; ?>

<div class="flex items-center justify-center min-h-screen bg-slate-50">
    <div class="max-w-lg w-full px-4">
        <form wire:submit='createListeningParty' class="space-y-6">
            <x-input wire:model="mediaUrl"
                     description="{{__('app.episode.description')}}"
                     label="{{__('app.episode.url')}}"
                     placeholder="{{__('app.episode.url')}}">
            </x-input>
            <x-input wire:model="name"
                     label="{{__('app.listening_party.name')}}"
                     placeholder="{{__('app.listening_party.name')}}">
            </x-input>
            <x-datetime-picker wire:model="startTime"
                               label="{{__('app.listening_party.start_time')}}"
                               placeholder="{{__('app.listening_party.start_time')}}">
            </x-datetime-picker>
            <x-button wire:click="createListeningParty()" primary>{{__('app.listening_party.create')}}</x-button>
        </form>
    </div>
</div>
