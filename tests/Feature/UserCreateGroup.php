<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCreateGroup extends TestCase
{
    use WithFaker;

    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function require_name_for_group()
    {
        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/api/group/create', []);

        /** Submission has failed validation. */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name'
        ]);
    }

    /** @test */
    public function able_to_create_groups()
    {
        $groupName = $this->faker->name;

        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/api/group/create', [
                'name' => $groupName
            ]);

        $response->assertOk();
        $response->assertSee('Group created successfully.');

        $this->assertTrue(!! Group::where('name', $groupName)->first());
    }

    /** @test */
    public function limit_only_one_group()
    {
        /** Create group for user. */
        $group = factory(Group::class)->create();

        $this->user->group_id = $group->id;

        $this->user->save();

        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/api/group/create', [
                'name' => $this->faker->name
            ]);

        /** User already has group, so creation is rejected. */
        $response->assertStatus(422);

        $response->assertSee('Users may only have one group.');
    }

    /** @test */
    public function check_for_existing_groups()
    {
        $groupName = $this->faker->name;

        $group = factory(Group::class)->create([
            'name' => $groupName
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/api/group/create', [
                'name' => $groupName
            ]);

        /** Group with that name already exists, so validation fails. */
        $response->assertStatus(422);
        $response->assertSee('That group name is unavailable.');
    }
}
