<?php

use App\Jobs\ProcessPodcastUrl;
use App\Models\Episode;
use App\Models\ListeningParty;

test('it processes RSS feed XML', function () {
    $ep = Episode::factory()->create(['title' => '']);
    $lp = ListeningParty::factory()->create(['episode_id' => $ep->id]);

    expect($ep->podcast()->count())->toBe(0);
    ProcessPodcastUrl::dispatch('https://feeds.simplecast.com/sY509q85', $lp, $ep);

    $ep->refresh();
    $lp->refresh();

    expect($ep->title)->not->toBe('')
        ->and($ep->podcast()->count())->toBe(1)
        ->and($lp->end_time)->not->toBeNull();
});
