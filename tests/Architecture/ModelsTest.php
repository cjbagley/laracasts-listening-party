<?php

use Illuminate\Database\Eloquent\Model;

test('models')
    ->expect('App\Models')
    ->toBeClasses()
    ->ignoring('App\Models\Traits');

test('models extends base model')
    ->expect('App\Models')
    ->toExtend(Model::class)
    ->ignoring('App\Models\Traits');

test('model traits')
    ->expect('App\Models\Traits')
    ->toBeTraits()
    ->toOnlyBeUsedIn('App\Models');
