<?php

declare(strict_types=1);

use App\Http\Controllers\Controller as AbstractController;

test('controllers')
    ->expect('App\Http\Controllers')
    ->toBeClasses()
    ->toBeFinal()
    ->ignoring(AbstractController::class);
