<?php

use App\Models\ListeningParty;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public $startTime;


    public function createListeningParty()
    {

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
            <x-input wire:model="name" placeholder="Listening Party Name"/>
            <x-input wire:model="mediaURL" placeholder="Podcast Episode URL"
                     description="Direct Episode Link or Youtube Link, RSS Feeds will grab the latest episode"/>
            <x-datetime-picker wire:model="startTime" placeholder="Listening Party Start Time"/>
            <x-button emerald type="submit" label="Create Listening Party"/>
        </form>
    </div>
</div>
