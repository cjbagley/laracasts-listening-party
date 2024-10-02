<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->middleware('web');

require __DIR__.'/auth.php';
