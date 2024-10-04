<?php

declare(strict_types=1);

use Illuminate\Contracts\Queue\ShouldQueue;

test('jobs')
    ->expect('App\Jobs')
    ->toBeClasses()
    ->toImplement(ShouldQueue::class)
    ->toHaveMethod('handle');
