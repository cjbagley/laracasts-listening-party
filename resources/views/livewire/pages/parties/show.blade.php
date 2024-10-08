<?php

use App\Models\ListeningParty;
use Livewire\Volt\Component;

new class extends Component {
    public ListeningParty $listeningParty;

    public function mount(ListeningParty $listeningParty)
    {
        $this->listening_party = $listeningParty->load('episode.podcast');
    }
}; ?>

<div x-data="{
            audio: null,
            isLoading: true,
            isLive: false,
            isPlaying: false,
            isReady: false,
            currentTime: 0,
            countdownText: '',
            secondsInDay: 86400,
            secondsInHour: 3600,
            secondsInMinute: 60,
            startTimestamp: {{ $this->listening_party->start_time->timestamp }},

            initAudioPlayer() {
                this.audio = this.$refs.audioPlayer;

                this.audio.addEventListener('loadedmetadata', (event) => {
                    this.isLoading = false;
                    this.checkAndUpdate();
                });

                this.audio.addEventListener('timeupdate', () => {
                    this.currentTime = this.audio.currentTime;
                });

                this.audio.addEventListener('play', () => {
                    this.isPlaying = true;
                });

                this.audio.addEventListener('pause', () => {
                    this.isPlaying = false;
                });

                this.audio.src = '{{ $this->listening_party->episode->media_url }}'
                this.audio.preload = 'auto';
            },

            nowTimestamp() {
                return Math.floor(Date.now() / 1000);
            },

            checkAndUpdate() {
                const timeUntilStart = this.startTimestamp - this.nowTimestamp();

                if (timeUntilStart <= 0 && !this.isPlaying) {
                    this.isLive = true;
                    if (this.isReady) {
                        this.audio.play().catch(error => console.error('Playback failed:', error));
                    }
                }
                if (timeUntilStart > 0) {
                    const days = Math.floor(timeUntilStart / this.secondsInDay);
                    const hours = Math.floor((timeUntilStart % this.secondsInDay) / this.secondsInHour);
                    const minutes = Math.floor((timeUntilStart % this.secondsInHour) / this.secondsInMinute);
                    const seconds = timeUntilStart % this.secondsInMinute;

                    this.countdownText = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                }
            },

            playAudio() {
                const elapsedTime = Math.max(0, this.nowTimestamp() - this.startTimestamp);
                this.audio.currentTime = elapsedTime;
                this.audio.play().catch(error => {
                    console.error('Playback failed:', error);
                    this.isPlaying = false;
                });
            },

            joinAndBeReady() {
                this.isReady = true;
                this.audio.play().then(() => {
                    this.audio.pause();
                }).catch(error => {
                    console.error('Playback failed:', error);
                    this.isPlaying = false;
                });
            },

            formatTime(seconds) {
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = Math.floor(seconds % 60);
                return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
            }
        }" x-init="initAudioPlayer">
    @if($listeningParty->end_time === null)
        <div wire:poll.5s
             class="flex items-center justify-center p-6 font-serif text-lg">{{__('app.listening_party.preparing', ['name' => $this->listening_party->name])}}</div>
    @else
        <audio x-ref="audioPlayer" :src="'{{ $this->listening_party->episode->media_url}}'" preload="auto"></audio>
        <div x-show="!isLive"
             class="flex items-center justify-center min-h-screen bg-emerald-50">
            <div class="w-full max-w-2xl shadow-lg rounded-lg bg-white p-8">
                <div class="flex items-center space-x-4">
                    @if($this->listening_party->episode->podcast?->artwork_url)
                        <div class="shrink-0">
                            <x-avatar src="{{ $this->listening_party->episode->podcast->artwork_url }}"
                                      alt="Podcast Network"
                                      size="xl"
                                      rounded="sm">
                            </x-avatar>
                        </div>
                    @endif
                    <div class="flex justify-between items-center w-full">
                        <x-listening-party-info :listening-party="$this->listening_party"/>
                        <p class="accent-slate-700 text-lg font-bolder">
                            {{__('app.listening_party.countdown')}}<span x-text="countdownText"></span>
                        </p>
                    </div>
                </div>
                <x-button x-show="!isReady"
                          class="w-full mt-8"
                          @click="joinAndBeReady()">{{__('app.listening_party.join')}}</x-button>
                <p x-show="isReady"
                   class="text-lg text-green-600 font-bolder text-center">{{__('app.listening_party.ready')}}</p>
            </div>
        </div>
        <div x-show="isLive">
            <div>{{ $this->listening_party->podcast->title }}</div>
            <div>{{ $this->listening_party->episode->title }}</div>
            <div>Current Time: <span x-text="formatTime(currentTime)"></span></div>
            <div>Start Time: {{ $this->listening_party->start_time }}</div>
            <div x-show="isLoading">{{ __('app.loading') }}</div>
        </div>
    @endif
</div>
