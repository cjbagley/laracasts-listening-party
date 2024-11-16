<?php

use App\Models\ListeningParty;
use Carbon\Carbon;
use Livewire\Volt\Component;

new class extends Component {
    public ListeningParty $listening_party;

    public bool $is_finished = false;

    public function mount(ListeningParty $listeningParty)
    {
        $this->listening_party = $listeningParty->load('episode.podcast');
        if (!$this->listening_party->is_active) {
            $this->is_finished = true;
        }

        if ($this->listening_party->end_time !== null && Carbon::now()->greaterThan($this->listening_party->end_time)) {
            $this->is_finished = true;
        }
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
            endTimestamp: {{ $this->listening_party->end_time?->timestamp }},

            init() {
                this.startCountdown();
                if (this.$refs.audioPlayer && !this.isFinished) {
                    this.initAudioPlayer();
                }
            },

            startCountdown(){
               this.checkAndUpdate();
               setTimeout(() => this.checkAndUpdate(), 1000);
            },

            initAudioPlayer() {
                this.audio = this.$refs.audioPlayer;
                this.audio.addEventListener('loadedmetadata', (event) => {
                    this.isLoading = false;
                    this.checkAndUpdate();
                });

                this.audio.addEventListener('timeupdate', () => {
                    this.currentTime = this.audio.currentTime;
                    if (! this.endTimestamp) {
                        return;
                    }

                    if (this.currentTime >= (this.endTimestamp - this.startTimestamp)) {
                        this.finishListeningParty();
                    }
                });

                this.audio.addEventListener('play', () => {
                    this.isPlaying = true;
                    this.isReady = true;
                });

                this.audio.addEventListener('pause', () => {
                    this.isPlaying = false;
                });

                this.audio.addEventListener('ended', () => {
                    this.finishListeningParty();
                });

                this.audio.src = '{{ $this->listening_party->episode->media_url }}'
                this.audio.preload = 'auto';
            },

            nowTimestamp() {
                return Math.floor(Date.now() / 1000);
            },

            elapsedTime() {
                return Math.max(0, this.nowTimestamp() - this.startTimestamp);
            },

            finishListeningParty() {
                $wire.is_finished = true;
                $wire.$refresh();
                this.isPlaying = false;
                if (this.audio) {
                    this.audio.pause();
                }
            },

            checkAndUpdate() {
                const timeUntilStart = this.startTimestamp - this.nowTimestamp();

                if(this.isFinished) {
                    return;
                }

                if (timeUntilStart > 0) {
                    const days = Math.floor(timeUntilStart / this.secondsInDay);
                    const hours = Math.floor((timeUntilStart % this.secondsInDay) / this.secondsInHour);
                    const minutes = Math.floor((timeUntilStart % this.secondsInHour) / this.secondsInMinute);
                    const seconds = timeUntilStart % this.secondsInMinute;

                    this.countdownText = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                    setTimeout(() => this.checkAndUpdate(), 1000);
                    return;
                }

                this.currentTime = this.elapsedTime();
                this.isLive = true;
                if (this.isReady) {
                    this.playAudio();
                }
            },

            playAudio() {
                if (!this.audio) {
                    return;
                }
                this.audio.currentTime = this.elapsedTime();
                this.audio.play().catch(error => {
                    console.error('Playback failed:', error);
                    this.isPlaying = false;
                    this.isReady = false;
                });
            },

            joinAndBeReady() {
                this.isReady = true;

                if (!this.audio) {
                    return;
                }

                if (!this.isLive) {
                    return;
                }

                if (this.isFinished) {
                    return;
                }

                this.playAudio();
            },

            formatTime(seconds) {
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = Math.floor(seconds % 60);
                return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
            }
        }" x-init="init()">
    @if($this->listening_party->end_time === null)
        <div wire:poll.5s
             class="flex items-center justify-center p-6 font-serif text-lg">{{__('app.listening_party.preparing', ['name' => $this->listening_party->name])}}</div>
    @elseif($is_finished)
        <div class="flex items-center justify-center min-h-screen bg-emerald-50">
            <div class="w-full max-w-2xl p-8 mx-8 text-center bg-white rounded-lg shadow-lg">
                <h2 class="mb-4 text-2xl font-bold text-slate-900">{{ __('app.listening_party.ended.header') }}</h2>
                <p class="text-slate-600">{{ __('app.listening_party.ended.thanks', ['listeningparty' => $this->listening_party->name]) }}</p>
                <p class="mt-2 text-slate-600">{{__('app.listening_party.ended.podcast', ['podcast' => $this->listening_party->podcast->title]) }}</p>
            </div>
        </div>
    @else
        <audio x-ref="audioPlayer" :src="'{{ $this->listening_party->episode->media_url}}'" preload="auto"></audio>
        <div x-cloak x-show="!isLive"
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
        <div x-cloak
             class="flex items-center justify-center min-h-screen bg-emerald-50"
             x-show="isLive">

            <div x-show="!isLoading" class="w-full max-w-2xl shadow-lg rounded-lg bg-white p-8">
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
                    <div class="flex flex-col justify-between w-full">
                        <x-listening-party-info :listening-party="$this->listening_party"/>
                        <div>Current Time: <span x-text="formatTime(currentTime)"></span></div>
                        <div>Start Time: {{ $this->listening_party->start_time }}</div>
                    </div>
                </div>
            </div>
            <div x-show="isLoading">{{ __('app.loading') }}</div>
        </div>
    @endif
</div>
