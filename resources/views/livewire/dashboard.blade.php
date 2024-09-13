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
        @if($listeningParties->isEmpty())
            <div>No awwdio listening parties started yet... 🥲</div>
        @else
            @foreach($listeningParties as $listeningParty)
                <div wire:key="{{ $listeningParty->id }}">
                    <x-avatar
                        src="{{ $listeningParty->episode->podcast->artwork_url }}"
                        size="xl"
                        rounded="full"
                    />
                    <p>{{ $listeningParty->name }}</p>
                    <p>{{ $listeningParty->episode->title }}</p>
                    <p>{{ $listeningParty->episode->podcast->title }}</p>
                    <p>{{ $listeningParty->start_time }}</p>
                </div>
            @endforeach
        @endif
    </div>
</div>
