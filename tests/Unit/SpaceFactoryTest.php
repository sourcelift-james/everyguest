<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\Space;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SpaceFactoryTest extends TestCase
{
    private $space;

    public function setUp(): void
    {
        parent::setUp();

        $this->space = factory(Space::class)->create();
    }

    /** @test */
    public function has_group()
    {
        $this->assertIsInt($this->space->group_id);
        $this->assertTrue(!! Group::find($this->space->group_id));
    }

    /** @test */
    public function has_name()
    {
        $this->assertIsString($this->space->name);
    }

    /** @test */
    public function has_capacity()
    {
        $this->assertIsInt($this->space->capacity);
    }

    /** @test */
    public function has_accommodations()
    {
        $this->assertIsString($this->space->accommodations);
    }

    /** @test */
    public function has_notes()
    {
        $this->assertIsString($this->space->notes);
    }
}
