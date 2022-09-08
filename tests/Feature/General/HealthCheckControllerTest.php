<?php

namespace Tests\Feature\General;

use Tests\TestCase;

class HealthCheckControllerTest extends TestCase
{
    public function test_health_check_endpoint_returns_http_status_ok()
    {
        $response = $this->get(route('healthcheck'));

        $response
            ->assertOk()
            ->assertExactJson([
                'status' => 'ok',
            ]);
    }
}
