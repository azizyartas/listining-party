<?php

use App\Jobs\ProcessPodcastUrl;
use App\Models\Episode;
use App\Models\ListeningParty;
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;

new class extends Component {
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|url')]
    public string $mediaURL = '';

    #[Validate('required')]
    public $startTime;

    public function createListeningParty()
    {
        $this->validate();

        $episode = Episode::create([
            'media_url' => $this->mediaURL,
        ]);

        $listeningParty = ListeningParty::create([
            'episode_id' => $episode->id,
            'name' => $this->name,
            'start_time' => $this->startTime,
        ]);

        ProcessPodcastUrl::dispatch($this->mediaURL, $listeningParty, $episode);

        return redirect()->route('parties.show', $listeningParty);
    }

    public function with(): array
    {
        return [
            'listeningParties' => ListeningParty::where('is_active', true)->orderBy('start_time')->with('episode.podcast')->get(),
        ];
    }
}; ?>

<div class="min-h-screen bg-emerald-50 flex flex-col pt-8">
    {{-- Top Half: Create New Listening Party Form --}}
    <div class="flex items-center justify-center p-4">
        <div class="w-full max-w-lg">
            <x-card shadow="lg" rounded="lg" class="bg-white">
                <h2 class="text-lg font-bold font-serif text-center">Let's listen together.</h2>
                <form wire:submit="createListeningParty" class="space-y-6 mt-6">
                    <x-input
                        wire:model="name"
                        placeholder="Listening Party Name"
                    />
                    <x-input
                        wire:model="mediaURL"
                        placeholder="Podcast RSS Feed URL"
                        description="Entering RSS Feed URL will grab the latest episode"
                    />
                    <x-datetime-picker
                        wire:model="startTime"
                        placeholder="Listening Party Start Time"
                        :min="now()->subDays(1)"
                    />
                    <x-button
                        class="w-full"
                        type="submit"
                        label="Create Listening Party"
                    />
                </form>
            </x-card>
        </div>
    </div>
    {{-- Bottom Half: Existing Listening Parties --}}
    <div class="my-20">
        <div class="max-w-lg mx-auto">
            <h3 class="font-serif mb-4 font-bold text-[0.9rem]">Ongoing Listening Parties</h3>
            <div class="bg-white rounded-lg shadow-lg">
                @if($listeningParties->isEmpty())
                    <div>No awwdio listening parties started yet... 🥲</div>
                @else
                    @foreach($listeningParties as $listeningParty)
                        <div wire:key="{{ $listeningParty->id }}">
                            <a href="{{ route('parties.show', $listeningParty) }}" class="block">
                                <div
                                    class="flex items-center justify-between p-4 border-b border-gray-200 hover:bg-gray-50 transition-all duration-150 ease-in-out">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <x-avatar
                                                src="{{ $listeningParty->episode->podcast->artwork_url }}"
                                                size="xl"
                                                rounded="small"
                                                alt="Podcast Artwork"
                                            />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-900 truncate">
                                                {{ $listeningParty->name }}
                                            </div>
                                            <div class="text-sm text-gray-600 truncate max-w-xs">
                                                {{ $listeningParty->episode->title }}
                                            </div>
                                            <div class="text-xs text-gray-400 truncate">
                                                {{ $listeningParty->episode->podcast->title }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1"
                                                 x-data="{
                                                    startTime: '{{ $listeningParty->start_time->toIso8601String() }}',
                                                    countdownText: '',
                                                    isLive: {{ $listeningParty->start_time->isPast() && $listeningParty->is_active ? 'true' : 'false' }},
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
                                                            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                                            this.countdownText = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                                                            this.isLive = false;
                                                        }
                                                    }
                                                }"
                                                 x-init="updateCountdown();
                                                 setInterval(() => updateCountdown(), 1000);"
                                            >
                                                <div x-show="isLive">
                                                    <x-badge flat rose label="LIVE">
                                                        <x-slot name="prepend" class="relative flex items-center w-2 h-2">
                                                            <span class="absolute inline-flex w-full h-full rounded-full opacity-75 bg-rose-500 animate-ping"></span>
                                                            <span class="relative inline-flex w-2 h-2 rounded-full bg-rose-500"></span>
                                                        </x-slot>
                                                    </x-badge>
                                                </div>
                                                <div x-show="!isLive">
                                                    Starts in: <span x-text="countdownText"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <x-button flat xs class="w-20">Join</x-button>
                                    </div>
                                </div>
                            </a>

                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
