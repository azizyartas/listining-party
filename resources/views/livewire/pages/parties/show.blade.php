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

<div>
    @if($listeningParty->end_time === null)
        <div class="flex items-center justify-center p-6 font-serif text-sm" wire:poll.5s>
            Creating your <span class="font-bold">{{ $listeningParty->name }}</span> listening party...
        </div>
    @else
        <div x-data="{
            audio: null,
            isLoading: true,
            currentTime: 0,
            startTimestamp: {{ $listeningParty->start_time->timestamp }},

            initializeAudioPlayer() {
                this.audio = this.$refs.audioPlayer;
                console.log(this.startTimestamp);
                this.audio.addEventListener('loadedmetadata', () => {
                    this.isLoading = false
                    this.checkAndPlayAudio();
                });

                this.audio.addEventListener('timeupdate', () => {
                    this.currentTime = this.audio.currentTime;
                });
            },

            checkAndPlayAudio() {
                const elapsedTime = Math.max(0, Math.floor(Date.now() / 1000)) - this.startTimestamp;
                if (elapsedTime > 0) {
                    this.audio.currentTime = elapsedTime;
                    this.isLoading = false;
                    this.audio.play().catch(error => console.error('Playback failed:', error));
                } else {
                    this.isLoading = true;
                    setTimeout(() => this.checkAndPlayAudio(), 1000);
                }
            },

            formatTime(seconds) {
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = Math.floor(seconds % 60);
                return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
            }
        }" x-init="initializeAudioPlayer()">
            <audio x-ref="audioPlayer" :src="'{{ $listeningParty->episode->media_url }}'" preload="auto"></audio>
            <div>{{ $listeningParty->podcast->title }}</div>
            <div>{{ $listeningParty->episode->title }}</div>
            <div>Current Time: <span x-text="formatTime(currentTime)"></span></div>
            <div>Start Time: {{ $listeningParty->start_time }}</div>
            <div x-show="isLoading">Loading...</div>
        </div>
    @endif
</div>
