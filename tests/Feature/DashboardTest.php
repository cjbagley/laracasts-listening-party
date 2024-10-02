<?php

it('loads dashboard page successfully', function () {
    $this->get('/')->assertStatus(200);
});
