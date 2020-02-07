<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\Invitation;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvitationFactoryTest extends TestCase
{
    private $invitation;

    public function setUp(): void
    {
        parent::setUp();

        $this->invitation = factory(Invitation::class)->create();
    }

    /** @test */
    public function has_group()
    {
        $this->assertIsInt($this->invitation->group_id);
        $this->assertTrue(!! Group::find($this->invitation->group_id));
    }

    /** @test */
    public function has_creator()
    {
        $this->assertIsInt($this->invitation->creator_id);
        $this->assertTrue(!! User::find($this->invitation->creator_id));
    }

    /** @test */
    public function has_token()
    {
        $this->assertIsString($this->invitation->token);
    }
}
