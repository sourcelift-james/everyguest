<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Space;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationFactoryTest extends TestCase
{
    private $reservation;

    public function setUp(): void
    {
        parent::setUp();

        $this->reservation = factory(Reservation::class)->create();
    }

    /** @test */
    public function has_guest()
    {
        $this->assertIsInt($this->reservation->guest_id);
        $this->assertTrue(!! Guest::find($this->reservation->guest_id));
    }

    /** @test */
    public function has_space()
    {
        $this->assertIsInt($this->reservation->space_id);
        $this->assertTrue(!! Space::find($this->reservation->space_id));
    }

    /** @test */
    public function has_group()
    {
        $this->assertIsInt($this->reservation->group_id);
        $this->assertTrue(!! Group::find($this->reservation->group_id));
    }

    /** @test */
    public function has_start_and_end_times()
    {
        $this->assertTrue(!! strtotime($this->reservation->starts_at));
        $this->assertTrue(!! strtotime($this->reservation->ends_at));
    }

    /** @test */
    public function has_notes()
    {
        $this->assertIsString($this->reservation->notes);
    }
}
