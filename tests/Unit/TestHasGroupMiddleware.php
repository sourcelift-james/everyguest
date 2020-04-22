<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TestHasGroupMiddleware extends TestCase
{
    /** @test */
    public function open_route_with_group()
    {
        $user = factory(User::class)->states('member')->create();
    }

    /** @test */
    public function open_route_without_group()
    {

    }
}
