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
                ->whereNotNull('end_time')
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
        <div class="max-w-lg mx-auto">
            <h3 class="mb-8 text-lg font-bold font-serif">{{ __('app.listening_party.ongoing')  }}</h3>
            <div class="bg-white rounded-lg shadow-lg">
                @forelse ($listening_parties as $listening_party)
                    <div wire:key="{{ $listening_party->id }}">
                        <a href="{{ route('parties.show', $listening_party) }}" class="block">
                            <div
                                class="flex items-center justify-between p-4 border-b border-gray-200 hover:bg-gray-50 transition duration-150 ease-in-out">
                                <div class="flex items-center space-x-4">
                                    @if($listening_party->episode->podcast?->artwork_url)
                                        <div class="shrink-0">
                                            <x-avatar src="{{ $listening_party->episode->podcast->artwork_url }}"
                                                      alt="Podcast Network"
                                                      size="xl"
                                                      rounded="sm">
                                            </x-avatar>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[0.9rem] text-base font-semibold truncate text-slate-500">{{ $listening_party->name }}</p>
                                        <p class="text-sm truncate text-slate-500 max-w-xs">{{ $listening_party->episode->title }}</p>
                                        <p class="text-slate-400 uppercase tracking-tight text-xs">{{ $listening_party->podcast->title }}</p>
                                        <p class="mt-1 text-xs">{{ $listening_party->start_time }}</p>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1" x-data="{
                                        startTime: '{{ $listening_party->start_time->toIso8601String() }}',
                                        countdownText: '',
                                        isLive: {{ $listening_party->start_time->isPast() && $listening_party->is_active ? 'true': 'false' }},
                                        updateCountdown() {
                                            const start = new Date(this.startTime).getTime();
                                            const now = new Date().getTime();
                                            const distance = start - now;
                                            if (distance < 0) {
                                                this.countdownText = 'Started';
                                                this.isLive = true;
                                            } else {
                                                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                                const seconds = Math.floor((distance % (1000 * 60)) / (1000));
                                                this.countdownText = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                                            }
                                        }
                                    }" x-init="updateCountdown(); setInterval(() => updateCountdown(), 1000);">
                                        <div x-show="isLive">
                                            <x-badge flat rose label="Live"></x-badge>
                                        </div>
                                        <div x-show="!isLive">
                                            Starts in <span x-text="countdownText"></span>
                                        </div>
                                    </div>
                                </div>
                                <x-button flat xs class="w-20">Join</x-button>
                            </div>
                        </a>
                    </div>
                @empty
                    <div
                        class="flex items-center justify-center p-6 font-serif text-sm">{{__('app.listening_party.empty')}}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
