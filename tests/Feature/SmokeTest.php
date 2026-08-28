<?php

namespace Tests\Feature;

use Tests\TestCase;

class SmokeTest extends TestCase
{
    public function test_home_page_renders(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Project Expedition Destinations');
    }

    public function test_health_check_responds(): void
    {
        $this->get('/up')->assertOk();
    }
}
