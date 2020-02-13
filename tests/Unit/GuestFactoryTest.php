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
    public function has_first_name()
    {
        $this->assertIsString($this->guest->first);
    }

    /** @test */
    public function has_last_name()
    {
        $this->assertIsString($this->guest->last);
    }

    /** @test */
    public function has_contact_information()
    {
        $this->assertIsString($this->guest->phone);
        $this->assertIsString($this->guest->email);
    }

    /** @test */
    public function has_address_components()
    {
        $this->assertIsString($this->guest->address);
        $this->assertIsString($this->guest->city);
        $this->assertIsString($this->guest->state);
        $this->assertIsString($this->guest->zip);
    }

    /** @test */
    public function has_arrival_method()
    {
        $this->assertIsString($this->guest->arrivalMethod);
    }

    /** @test */
    public function has_arrival_time()
    {
        $this->assertTrue(!! $this->guest->arrivalTime);
    }

    /** @test */
    public function has_departure_method()
    {
        $this->assertIsString($this->guest->departureMethod);
    }

    /** @test */
    public function has_departure_time()
    {
        $this->assertTrue(!! $this->guest->departureTime);
    }

    /** @test */
    public function has_group()
    {
        $this->assertIsInt($this->guest->group_id);
        $this->assertTrue(!! Group::find($this->guest->group_id));
    }
}
