<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GroupMemberActions extends TestCase
{
    private $group;
    private $member;
    private $owner;

    public function setUp(): void
    {
        parent::setUp();

        $this->owner = factory(User::class)->create();

        $this->group = factory(Group::class)->create([
            'owner_id' => $this->owner->id
        ]);

        $this->member = factory(User::class)->create([
            'group_id' => $this->group->id
        ]);

        $this->owner->group_id = $this->group->id;

        $this->owner->save();
    }

    /** @test */
    public function members_can_fetch_member_details()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('GET', '/api/group/' . $this->group->id . '/members/' . $this->member->id);

        $response->assertOk();

        $uninitializedMember = User::find($this->member->id);

        $response->assertSee($uninitializedMember);
    }

    /** @test */
    public function rejects_if_group_does_not_exist()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('GET', '/api/group/' . 1600 . '/members/' . $this->member->id);

        $response->assertStatus(422);

        $response->assertSee('No group found');
    }

    /** @test */
    public function rejects_if_user_does_not_belong_to_group()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('GET', '/api/group/' . 1600 . '/members/' . $this->member->id);

        $response->assertStatus(422);

        $response->assertSee('No group found');
    }

    /** @test */
    public function reject_if_member_does_not_exist()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('GET', '/api/group/' . $this->group->id . '/members/' . 1600);

        $response->assertStatus(422);

        $response->assertSee('Selected member does not exist.');
    }

    /** @test */
    public function reject_if_member_does_not_belong_to_group()
    {
        $nonMember = factory(User::class)->create();

        $response = $this->actingAs($this->owner, 'api')
            ->json('GET', '/api/group/' . $this->group->id . '/members/' . $nonMember->id);

        $response->assertStatus(422);

        $response->assertSee('Selected member does not belong to selected group.');
    }

    /** @test */
    public function owner_can_remove_member()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/members/' . $this->member->id . '/remove');

        $response->assertOk();

        $uninitializedMember = User::find($this->member->id);

        $this->assertTrue(! $uninitializedMember->group_id);
    }

    /** @test */
    public function reject_removal_if_group_does_not_exist()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/group/' . 1600 . '/members/' . $this->member->id . '/remove');

        $response->assertStatus(422);
        $response->assertSee('Group does not exist.');
    }

    /** @test */
    public function reject_removal_if_user_is_not_owner()
    {
        $nonOwner = factory(User::class)->create();

        $response = $this->actingAs($nonOwner, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/members/' . $this->member->id . '/remove');

        $response->assertStatus(401);
        $response->assertSee('Unauthorized access.');
    }

    /** @test */
    public function reject_removal_if_member_does_not_exist()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/members/' . 1600 . '/remove');

        $response->assertStatus(422);
        $response->assertSee('Member does not exist.');
    }

    /** @test */
    public function reject_removal_if_member_does_not_belong_to_group()
    {
        $nonMember = factory(User::class)->create();

        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/members/' . $nonMember->id . '/remove');

        $response->assertStatus(422);
        $response->assertSee('That user is not in your group.');
    }
}
