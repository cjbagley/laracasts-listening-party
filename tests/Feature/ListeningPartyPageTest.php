<?php

use App\Models\Episode;
use App\Models\ListeningParty;

it('loads listening party page successfully', function () {
    $ep = Episode::factory()->create();
    $lp = ListeningParty::factory()->create(['episode_id' => $ep->id]);

    $this->get(route('parties.show', $lp))
        ->assertStatus(200)
        ->assertSee($lp->name)
        ->assertSee(__('app.listening_party.preparing', ['name' => $lp->name]));
});
