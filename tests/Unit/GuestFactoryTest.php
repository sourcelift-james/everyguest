<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\Invitation;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Guest;

class GuestFactoryTest extends TestCase
{

    private $guest;

    public function setUp(): void
    {
        parent::setUp();

        $this->guest = factory(Guest::class)->create();
    }

    /** @test */
    public function has_name()
    {
        $this->assertIsString($this->guest->name);
    }

    /** @test */
    public function has_invitation()
    {
        $this->assertIsInt($this->guest->invitation_id);
        $this->assertTrue(!!Invitation::find($this->guest->invitation_id));
    }

    /** @test */
    public function has_contact_information()
    {
        $this->assertIsString($this->guest->phone);
        $this->assertIsString($this->guest->email);
    }

    /** @test */
    public function has_arrival_method()
    {
        $this->assertIsString($this->guest->arrMethod);
    }

    /** @test */
    public function has_group()
    {
        $this->assertIsInt($this->guest->group_id);
        $this->assertTrue(!! Group::find($this->guest->group_id));
    }
}
