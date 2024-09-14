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
            <h3 class="font-serif mb-8 font-bold">Ongoing Listening Parties</h3>
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
                                            <div class="text-sm text-gray-600 truncate">
                                                {{ $listeningParty->episode->title }}
                                            </div>
                                            <div class="text-xs text-gray-400 truncate">
                                                {{ $listeningParty->episode->podcast->title }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <p>{{ $listeningParty->start_time }}</p>
                                            </div>
                                        </div>
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
