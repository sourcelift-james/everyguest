<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Reservation;
use  App\Models\Space;
use App\Models\User;
use  App\Models\Guest;
use  App\Models\Invitation;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;


class GroupRelationshipsTest extends TestCase
{
    private $group;

    public function setUp(): void
    {
        parent::setUp();

        $this->group = factory(Group::class)->create();

        /** Members */
        factory(User::class, 3)->create([
            'group_id' => $this->group->id
        ]);

        /** Guests */
        factory(Guest::class, 5)->create([
            'group_id' => $this->group->id
        ]);

        /** Spaces */
        factory(Space::class, 5)->create([
            'group_id' => $this->group->id
        ]);

        /** Invitations */
        factory(Invitation::class, 2)->create([
            'group_id' => $this->group->id
        ]);

        /** Reservations */
        factory(Reservation::class, 5)->create([
            'group_id' => $this->group->id
        ]);
    }

    /** @test */
    public function has_owner()
    {
        $this->assertIsInt($this->group->owner_id);
        $this->assertTrue(!! User::find($this->group->owner_id));
    }

    /** @test */
    public function has_members()
    {
        $this->assertTrue($this->group->members()->get()->count() > 0);
        $this->assertTrue(class_basename($this->group->members()->first()) == 'User');
    }

    /** @test */
    public function has_guests()
    {
        $this->assertTrue($this->group->guests()->get()->count() > 0);
        $this->assertTrue(class_basename($this->group->guests()->first()) == 'Guest');
    }

    /** @test */
    public function has_spaces()
    {
        $this->assertTrue($this->group->spaces()->get()->count() > 0);
        $this->assertTrue(class_basename($this->group->spaces()->first()) == 'Space');
    }

    /** @test */
    public function has_invitations()
    {
        $this->assertTrue($this->group->invitations()->get()->count() > 0);
        $this->assertTrue(class_basename($this->group->invitations()->first()) == 'Invitation');
    }

    /** @test */
    public function has_reservations()
    {
        $this->assertTrue($this->group->reservations()->get()->count() > 0);
        $this->assertTrue(class_basename($this->group->reservations()->first()) == 'Reservation');
    }
}
