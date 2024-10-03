<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'home')->middleware('web');

Volt::route('/parties/{listeningParty}', 'pages.parties.show')->name('parties.show');

require __DIR__.'/auth.php';
