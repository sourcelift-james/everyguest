<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Invitation;
use App\Models\Guest;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvitationREST extends TestCase
{
    use WithFaker;

    private $group;
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->group = factory(Group::class)->create();

        $this->user = factory(User::class)->create([
            'group_id' => $this->group->id,
        ]);
    }

    /** @test */
    public function get_index()
    {
        factory(Invitation::class, 5)->create([
            'group_id' => $this->group->id
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/api/invitations');

        $response->assertOk();

        $invitationsQuery = Invitation::where('group_id', $this->group->id)->get();

        $response->assertSee($invitationsQuery);
    }

    /** @test */
    public function create_invitation()
    {
        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/api/invitations/create', [
                'name' => $this->faker->words(3, true)
            ]);

        $createdInvitation = Invitation::where('group_id', $this->group->id)->where('creator_id', $this->user->id)->first();

        $response->assertOk();

        $response->assertSee('Invitation created.');

        $this->assertInstanceOf(Invitation::class, $createdInvitation);
    }

    /** @test */
    public function create_invitation_with_blank_name()
    {
        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/api/invitations/create');

        $createdInvitation = Invitation::where('group_id', $this->group->id)->where('creator_id', $this->user->id)->first();

        $response->assertStatus(422);

        $this->assertNull($createdInvitation);
    }

    /** @test */
    public function create_invitation_with_non_date_expiration_field()
    {
        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/api/invitations/create', [
                'name' => $this->faker->words(3, true),
                'expiration' => 'babyface'
            ]);

        $createdInvitation = Invitation::where('group_id', $this->group->id)->where('creator_id', $this->user->id)->first();

        $response->assertStatus(422);

        $this->assertNull($createdInvitation);
    }

    /** @test */
    public function show_invitation()
    {
        $invitation = factory(Invitation::class)->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/api/invitations/' . $invitation->id);

        $response->assertOk();

        $freshFromDb = Invitation::find($invitation->id);

        $response->assertSee($freshFromDb);
    }

    /** @test */
    public function show_invitation_from_another_group()
    {
        $invitation = factory(Invitation::class)->create();

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/api/invitations/' . $invitation->id);

        $response->assertStatus(401);

        $response->assertSee('Unauthorized access.');
    }

    /** @test */
    public function update_invitation()
    {
        $invitation = factory(Invitation::class)->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/api/invitations/' . $invitation->id . '/update', [
                'details' => [
                    [
                        'label' => 'Birthday',
                        'type' => 'text',
                        'required' => true
                    ]
                ]
            ]);

        $response->assertOk();

        $freshFromDb = Invitation::find($invitation->id);

        $this->assertTrue($freshFromDb->details['birthday']['label'] == 'Birthday');
        $this->assertTrue($freshFromDb->details['birthday']['type'] == 'text');
        $this->assertTrue(!! $freshFromDb->details['birthday']['validation']);

        $response->assertSee('Invitation updated.');
    }

    /** @test */
    public function update_invitation_from_another_group()
    {
        $invitation = factory(Invitation::class)->create();

        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/api/invitations/' . $invitation->id . '/update', [
                'details' => [
                    [
                        'label' => 'Birthday',
                        'type' => 'text',
                        'required' => true
                    ]
                ]
            ]);

        $response->assertStatus(401);

        $response->assertSee('Unauthorized access.');
    }

    /** @test */
    public function delete_invitation()
    {
        $invitation = factory(Invitation::class)->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/api/invitations/' . $invitation->id . '/delete');

        $response->assertOk();

        $this->assertNull(Invitation::find($invitation->id));

        $response->assertSee('Invitation deleted.');
    }

    /** @test */
    public function delete_invitation_from_another_group()
    {
        $invitation = factory(Invitation::class)->create();

        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/api/invitations/' . $invitation->id . '/delete');

        $response->assertStatus(401);

        $response->assertSee('Unauthorized access.');

        $this->assertInstanceOf(Invitation::class, Invitation::find($invitation->id));
    }

    /** @test */
    public function display_invitation()
    {
        $invitation = factory(Invitation::class)->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->user->id,
        ]);

        $response = $this->json('GET', '/api/welcome/' . $invitation->token);

        $response->assertOk();

        $freshFromDb = Invitation::find($invitation->id);

        $response->assertSee($freshFromDb);
    }

    /** @test */
    public function display_invitation_that_doesnt_exist()
    {
        $invitation = factory(Invitation::class)->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->user->id,
        ]);

        $response = $this->json('GET', '/api/welcome/' . 1600);

        $response->assertStatus(404);

        $response->assertSee('Invitation not found.');
    }

    /** @test */
    public function display_invitation_that_has_expired()
    {
        $invitation = factory(Invitation::class)->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->user->id,
            'expired_at' => '2020-02-01 00:00:00'
        ]);

        $response = $this->json('GET', '/api/welcome/' . $invitation->token);

        $response->assertStatus(404);

        $response->assertSee('That invitation has expired.');
    }

    /** @test */
    public function submit_invitation()
    {
        $invitation = factory(Invitation::class)->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->user->id,
        ]);

        $guestInfo = [
            'first' => $this->faker->firstName,
            'last' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->safeEmail,
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'zip' => $this->faker->postcode,
            'arrivalMethod' => $this->faker->randomElement([
                'Flight', 'Rented or Owned Vehicle'
            ]),
            'arrivalTime' => '2020-02-10 12:00:00',
            'departureMethod' => $this->faker->randomElement([
                'Flight', 'Rented or Owned Vehicle'
            ]),
            'departureTime' => '2020-02-20 12:00:00'
        ];

        $response = $this->json('POST', '/api/welcome/' . $invitation->token, $guestInfo);

        $response->assertOk();

        $guest = Guest::where('first', $guestInfo['first'])->where('last', $guestInfo['last'])->first();

        $this->assertInstanceOf(Guest::class, $guest);

        $response->assertSee('Thank you for submitting your information.');
    }

//    /** @test */
//    public function submit_invitation_with_custom_text_field()
//    {
//
//    }
//
//    /** @test */
//    public function submit_invitation_with_custom_select_field()
//    {
//
//    }
//
//    /** @test */
//    public function submit_invitation_that_does_not_exist()
//    {
//
//    }
//
//    /** @test */
//    public function submit_invitation_that_has_expired()
//    {
//
//    }
//
//    /** @test */
//    public function submit_invitation_with_missing_fields()
//    {
//
//    }
}
