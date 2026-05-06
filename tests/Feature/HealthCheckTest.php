<?php

declare(strict_types=1);

test('health check endpoint returns ok', function () {
    $response = $this->get('/up');

    $response->assertStatus(200);
});
