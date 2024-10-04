<?php

use App\Jobs\ProcessPodcastUrl;
use Illuminate\Routing\Redirector;
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
    public string $startTime;

    public function createListeningParty(): Redirector
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

        ProcessPodcastUrl::dispatch($this->mediaUrl, $listening_party, $episode);

        return redirect()->route('parties.show', $listening_party, $episode);
    }

    public function with(): array
    {
        return [
            'listening_parties' => ListeningParty::where('is_active', '=', true)
                ->orderBy('start_time', 'asc')
                ->with('episode.podcast')
                ->get(),
        ];
    }
}; ?>

<div class="flex flex-col min-h-screen bg-emerald-50 pt-8">
    <div class="flex items-center justify-center pt-4">
        <div class="w-full max-w-lg">
            <x-card shadow="lg" rounded="lg">
                <h2 class="text-xl font-bold font-serif text-center">{{__('app.listening_party.heading')}}</h2>
                <form wire:submit='createListeningParty' class="space-y-6 mt-6">
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
                                       :min="now()->subDay()"
                                       label="{{__('app.listening_party.start_time')}}"
                                       placeholder="{{__('app.listening_party.start_time')}}">
                    </x-datetime-picker>
                    <x-button wire:click="createListeningParty()"
                              class="w-full"
                              primary>{{__('app.listening_party.create')}}</x-button>
                </form>
            </x-card>
        </div>
    </div>

    <div class="my-20">
        @forelse ($listening_parties as $listening_party)
            <div wire:key="{{ $listening_party->id }}">
                @if($listening_party->episode->podcast?->artwork_url)
                    <x-avatar src="{{ $listening_party->episode->podcast->artwork_url }}"
                              size="xl"
                              rounded="full">
                    </x-avatar>
                @endif
                <p>{{ $listening_party->name }}</p>
                <p>{{ $listening_party->episode->title }}</p>
                <p>{{ $listening_party->podcast->title }}</p>
                <p>{{ $listening_party->start_time }}</p>
            </div>
        @empty
            <div>{{__('app.listening_party.empty')}}</div>
        @endforelse
    </div>
</div>
