<?php

use App\Models\Episode;
use App\Models\ListeningParty;
use Livewire\Volt\Volt;

it('loads page component', function () {
    $ep = Episode::factory()->create();
    $lp = ListeningParty::factory()->create(['episode_id' => $ep->id]);

    Volt::test('pages.parties.show', [$lp])
        ->assertStatus(200)
        ->assertSeeText($lp->name);
});
