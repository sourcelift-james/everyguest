<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use App\Models\Group;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GroupInviteMember extends TestCase
{
    use WithFaker;

    private $group;
    private $invited;
    private $owner;

    public function setUp(): void
    {
        parent::setUp();

        $this->owner = factory(User::class)->create();

        $this->group = factory(Group::class)->create([
            'owner_id' => $this->owner->id
        ]);

        $this->invited = factory(User::class)->create();

        $this->owner->group_id = $this->group->id;

        $this->owner->save();
    }

    /** @test */
    public function owner_can_invite_new_member()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/invite', [
                'email' => $this->invited->email
            ]);

        $response->assertOk();

        $response->assertSee('New member added to group.');
    }

    /** @test */
    public function non_owner_cannot_invite()
    {
        $nonOwner = factory(User::class)->create([
            'group_id' => $this->group->id
        ]);

        $response = $this->actingAs($nonOwner, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/invite', [
                'email' => $this->invited->email
            ]);

        $response->assertStatus(401);
        $response->assertSee('Unauthorized access.');
    }

    /** @test */
    public function non_member_cannot_invite()
    {
        $nonMember = factory(User::class)->create([
            'group_id' => $this->group->id
        ]);

        $response = $this->actingAs($nonMember, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/invite', [
                'email' => $this->invited->email
            ]);

        $response->assertStatus(401);
        $response->assertSee('Unauthorized access.');
    }

    /** @test */
    public function reject_if_group_does_not_exist()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/group/' . 1600 . '/invite', [
                'email' => $this->invited->email
            ]);

        $response->assertStatus(422);
        $response->assertSee('Group does not exist.');
    }

    /** @test */
    public function reject_if_no_email_provided()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/invite');

        /** Failed Laravel validation. */
        $response->assertStatus(422);
    }

    /** @test */
    public function reject_if_invalid_email_provided()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/invite', [
                'email' => 'asdfasdf'
            ]);

        /** Failed Laravel validation. */
        $response->assertStatus(422);
    }

    /** @test */
    public function reject_if_invited_member_does_not_exist()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/invite', [
                'email' => 'example@example.com'
            ]);

        $response->assertStatus(422);

        $response->assertSee('Invited user does not exist.');
    }

    /** @test */
    public function reject_if_invited_member_is_already_in_a_group()
    {
        $groupMember = factory(User::class)->create([
            'group_id' => $this->group->id
        ]);

        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/group/' . $this->group->id . '/invite', [
                'email' => $groupMember->email
            ]);

        $response->assertStatus(422);

        $response->assertSee('Invited user is already in a group.');
    }
}
