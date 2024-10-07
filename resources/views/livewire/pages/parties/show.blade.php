<?php

use App\Models\ListeningParty;
use Livewire\Volt\Component;

new class extends Component {
    public ListeningParty $listeningParty;

    public function mount(ListeningParty $listeningParty)
    {
        $this->listeningParty = $listeningParty->load('episode.podcast');
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
            startTimestamp: {{ $listeningParty->start_time->timestamp }},

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

                this.audio.src = '{{ $listeningParty->episode->media_url }}'
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
                    console.log(this.secondsInDay);
                    console.log(days);
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
             class="flex items-center justify-center p-6 font-serif text-lg">{{__('app.listening_party.preparing', ['name' => (string)$listeningParty->name])}}</div>
    @else
        <audio x-ref="audioPlayer" :src="'{{ $listeningParty->episode->media_url}}'" preload="auto"></audio>
        <div x-show="!isLive"
             class="flex items-center justify-center min-h-screen bg-emerald-50">
            <div class="w-full max-w-2xl shadow-lg rounded-lg bg-white p-8">
                <p class="text-slate-900">The show will start in <span x-text="countdownText"></span></p>
            </div>

        </div>
        <div x-show="isLive">
            <div>{{ $listeningParty->podcast->title }}</div>
            <div>{{ $listeningParty->episode->title }}</div>
            <div>Current Time: <span x-text="formatTime(currentTime)"></span></div>
            <div>Start Time: {{ $listeningParty->start_time }}</div>
            <div x-show="isLoading">{{ __('app.loading') }}</div>
        </div>
    @endif
</div>
