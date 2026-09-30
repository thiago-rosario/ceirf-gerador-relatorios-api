<?php

test('the health check remains available as JSON', function () {
    $response = $this->getJson('/up');

    $response->assertOk()
        ->assertExactJson(['status' => 'up']);
});
