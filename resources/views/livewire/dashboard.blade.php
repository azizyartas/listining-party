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
            'listening_parties' => ListeningParty::all(),
        ];
    }
}; ?>

<div class="flex items-center justify-center min-h-screen bg-slate-50">
    <div class="max-w-lg w-full px-4">
        <form wire:submit="createListeningParty" class="space-y-6">
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
                emerald
                type="submit"
                label="Create Listening Party"
            />
        </form>
    </div>
</div>
