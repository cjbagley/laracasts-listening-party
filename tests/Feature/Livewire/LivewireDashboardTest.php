<?php

use App\Models\Episode;
use App\Models\ListeningParty;
use Livewire\Volt\Volt;

it('loads dashboard component', function () {
    Volt::test('dashboard')
        ->assertStatus(200)
        ->assertSeeText(__('app.listening_party.name'))
        ->assertSeeText(__('app.listening_party.start_time'))
        ->assertSeeText(__('app.listening_party.create'))
        ->assertSeeText(__('app.episode.description'))
        ->assertSeeText(__('app.episode.url'));
});

it("validates form 'name' input", function () {
    Volt::test('dashboard')->set('name', '')->assertHasErrors(['name' => 'required']);
});

it("validates form 'podcast url' input", function () {
    Volt::test('dashboard')->set('mediaUrl', '')->assertHasErrors(['mediaUrl' => 'required']);
    Volt::test('dashboard')->set('mediaUrl', 'test')->assertHasErrors(['mediaUrl' => 'url']);
});

it("validates form 'start time' input", function () {
    Volt::test('dashboard')->set('startTime', '')->assertHasErrors(['startTime' => 'required']);
});

it('successfully processes form', function (string $name, string $url, string $startTime) {
    expect(Episode::count())->toBe(0)
        ->and(ListeningParty::count())->toBe(0);

    Volt::test('dashboard')->set([
        'name' => $name,
        'mediaUrl' => $url,
        'startTime' => $startTime,
    ])
        ->call('createListeningParty')
        ->assertHasNoErrors()
        ->assertRedirect(route('parties.show', ListeningParty::first()));

    $listeningParty = ListeningParty::first();
    expect($listeningParty->name)->toBe($name)
        ->and($listeningParty->episode->media_url)->toBe($url)
        ->and($listeningParty->start_time)->toBe($startTime);

})->with([['Test', 'https://test.com', '2024-09-30T23:00:00']]);
