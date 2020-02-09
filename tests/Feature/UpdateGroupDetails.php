<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateGroupDetails extends TestCase
{
    use WithFaker;
    private $group;
    private $owner;

    public function setUp(): void
    {
        parent::setUp();

        $this->owner = factory(User::class)->create();

        $this->group = factory(Group::class)->create([
            'owner_id' => $this->owner->id
        ]);

        $this->owner->group_id = $this->group->id;

        $this->owner->save();
    }

    /** @test */
    public function owner_can_update_group_name()
    {
        $name = $this->faker->company;

        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/update', [
                'name' => $name,
                'owner' => $this->owner->id
            ]);

        $response->assertOk();

        $newGroupDetails = Group::find($this->group->id);

        $this->assertTrue($newGroupDetails->name == $name);
    }

    /** @test */
    public function owner_can_change_owner()
    {
        $newOwner = factory(User::class)->create([
            'group_id' => $this->group->id
        ]);

        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/update', [
                'name' => $this->group->name,
                'owner' => $newOwner->id
            ]);

        $response->assertOk();

        $newGroupDetails = Group::find($this->group->id);

        $this->assertTrue($newGroupDetails->owner_id == $newOwner->id);
    }

    /** @test */
    public function reject_if_group_does_not_exist()
    {
        $name = $this->faker->company;

        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/group/' . 1600 . '/update', [
                'name' => $name,
                'owner' => $this->owner->id
            ]);

        $response->assertStatus(422);
        $response->assertSee('Group not found');
    }

    /** @test */
    public function reject_if_user_is_not_owner_of_group()
    {
        $name = $this->faker->company;

        $nonOwner = factory(User::class)->create();

        $response = $this->actingAs($nonOwner, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/update', [
                'name' => $name,
                'owner' => $nonOwner->id
            ]);

        $response->assertStatus(401);
        $response->assertSee('Unauthorized access.');
    }

    /** @test */
    public function reject_if_group_name_is_already_in_use()
    {
        $name = $this->faker->company;

        $existingGroup = factory(Group::class)->create([
            'name' => $name
        ]);

        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/update', [
                'name' => $name,
                'owner' => $this->owner->id
            ]);

        $response->assertStatus(422);
        $response->assertSee('That name is already taken by another group.');
    }

    /** @test */
    public function reject_if_prospective_owner_does_not_exist()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/update', [
                'owner' => 1600,
                'name' => $this->group->name
            ]);

        $response->assertStatus(422);
        $response->assertSee('Selected new owner does not exist.');
    }

    /** @test */
    public function reject_if_prospective_owner_is_not_in_group()
    {
        $nonMember = factory(User::class)->create();

        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/update', [
                'owner' => $nonMember->id,
                'name' => $this->group->name
            ]);

        $response->assertStatus(422);
        $response->assertSee('Selected new owner does not belong to your group.');
    }
}
