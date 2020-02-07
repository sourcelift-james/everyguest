<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GroupMemberTest extends TestCase
{
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->states('member')->create();
    }

    /** @test */
    public function has_attributes()
    {
        $this->assertIsString($this->user->email);
        $this->assertIsString($this->user->name);
        $this->assertIsString($this->user->password);

        $this->assertIsInt($this->user->group_id);
    }
}
