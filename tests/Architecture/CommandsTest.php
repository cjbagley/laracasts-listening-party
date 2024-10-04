<?php

declare(strict_types=1);

use Illuminate\Console\Command;

test('commands')
    ->expect('App\Console\Commands')
    ->toBeClasses()
    ->toUseStrictTypes()
    ->toExtend(Command::class)
    ->toHaveMethod('handle');
