<?php

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
    Volt::test('dashboard')->set('podcastUrl', '')->assertHasErrors(['podcastUrl' => 'required']);
    Volt::test('dashboard')->set('podcastUrl', 'test')->assertHasErrors(['podcastUrl' => 'url']);
});

it("validates form 'start time' input", function () {
    Volt::test('dashboard')->set('startTime', '')->assertHasErrors(['startTime' => 'required']);
});
