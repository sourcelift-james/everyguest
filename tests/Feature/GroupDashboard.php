<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GroupDashboard extends TestCase
{
    private $group;

    public function setUp(): void
    {
        parent::setUp();
        $this->group = factory(Group::class)->create();
    }

    /**
     * Test that route delivers group details equal to what's found in the DB.
     * @test
     */
    public function group_exists()
    {
        $response = $this->actingAs($this->group->owner, 'api')
            ->json('GET', '/api/group/' . $this->group->id);

        $response->assertOk();

        $uninitializedGroup = Group::find($this->group->id);

        $response->assertSee($uninitializedGroup);
    }

    /**
     * Test validation that, if group doesn't exist, a 422 status code is returned.
     * @test
     */
    public function group_does_not_exist()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')
            ->json('GET', '/api/group/' . 1600);

        $response->assertStatus(404);
    }

    /**
     * Test that the API does not deliver group details to non-members.
     * @test
     */
    public function user_does_not_belong_to_group()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')
            ->json('GET', '/api/group/' . $this->group->id);

        $response->assertStatus(404);
    }

    /**
     * Fetch members of the group (for display on dashboard).
     * @test
     */
    public function fetches_group_members()
    {
        $user = factory(User::class)->create([
            'group_id' => $this->group->id
        ]);

        $response = $this->actingAs($user, 'api')
            ->json('GET', '/api/group/' . $this->group->id . '/members');

        $response->assertOk();

        $members = User::where('group_id', $this->group->id)->get();

        $response->assertSee($members);
    }

    /**
     * Test validation to check if group exists before fetching members.
     * Testing by using the API with a bogus group id and getting 422.
     * @test
     */
    public function invalid_group_id_does_not_fetch_members()
    {
        $user = factory(User::class)->create([
            'group_id' => $this->group->id
        ]);

        $response = $this->actingAs($user, 'api')
            ->json('GET', '/api/group/' . 1600 . '/members');

        $response->assertStatus(404);
    }

    /**
     * Ensure non-members cannot fetch group member info.
     * Use legitimate group id and legitimate User that does not belong to group.
     * Return 422.
     * @test
     */
    public function non_member_cannot_fetch_members()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')
            ->json('GET', '/api/group/' . $this->group->id . '/members');

        $response->assertStatus(404);

        $response->assertSee('Group not found.');
    }
}
