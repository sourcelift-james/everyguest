<?php
namespace Tests\Feature;

use App\Models\Group;
use App\Models\Space;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SpaceCrud extends TestCase
{
    use WithFaker;
    private $group;
    private $owner;
    private $member;
    private $space;
    private $nonMember;

    public function setUp(): void
    {
        parent::setUp();
        $this->owner = factory(User::class)->create();

        $this->group = factory(Group::class)->create([
            'owner_id' => $this->owner->id
        ]);

        $this->owner->group_id = $this->group->id;

        $this->member = factory(User::class)->create([
           'group_id' => $this->group->id
        ]);

        $this->nonMember = factory(User::class)->create();

        $this->space = factory(Space::class)->create([
            'group_id' => $this->group->id
        ]);
    }

    /** @test */
    public function member_can_fetch_space_list()
    {
        $response = $this->actingAs($this->member, 'api')
            ->json('GET', '/api/spaces');

        $response->assertOk();

        $response->assertSee(Space::where('group_id', $this->group->id)->get());
    }

    /** @test */
    public function owner_able_to_create_spaces()
    {
        $name = $this->faker->secondaryAddress;

        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/spaces/create', [
                'name' => $name,
                'capacity' => $this->faker->numberBetween(1,5),
                'accommodations' => $this->faker->sentences(3, true),
                'notes' => $this->faker->sentences(2, true)
            ]);

        $response->assertOk();

        $space = Space::where('name', $name)->first();

        $this->assertTrue(!! $space);

        $response->assertSee('Space created.');
    }

    /** @test */
    public function reject_creation_with_empty_fields()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/spaces/create', [
                'accommodations' => $this->faker->sentences(3, true),
                'notes' => $this->faker->sentences(2, true)
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function reject_creation_with_invalid_fields()
    {
        $name = $this->faker->secondaryAddress;

        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/spaces/create', [
                'name' => $name,
                'capacity' => 'jafa',
                'accommodations' => $this->faker->sentences(3, true),
                'notes' => $this->faker->sentences(2, true)
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function non_member_rejected_trying_to_create_space()
    {
        $name = $this->faker->secondaryAddress;

        $response = $this->actingAs($this->nonMember, 'api')
            ->json('POST', '/api/spaces/create', [
                'name' => $name,
                'capacity' => $this->faker->numberBetween(1,5),
                'accommodations' => $this->faker->sentences(3, true),
                'notes' => $this->faker->sentences(2, true)
            ]);

        $response->assertStatus(401);

        $response->assertSee('You must belong to a group to create a space.');
    }

    /** @test */
    public function non_owner_rejected_trying_to_create_space()
    {
        $name = $this->faker->secondaryAddress;

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/spaces/create', [
                'name' => $name,
                'capacity' => $this->faker->numberBetween(1,5),
                'accommodations' => $this->faker->sentences(3, true),
                'notes' => $this->faker->sentences(2, true)
            ]);

        $response->assertStatus(401);

        $response->assertSee('Only group owners can create and manage spaces.');
    }

    /** @test */
    public function reject_if_name_already_in_use()
    {
        $name = $this->faker->secondaryAddress;

        factory(Space::class)->create([
            'group_id' => $this->group->id,
            'name' => $name
        ]);

        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/spaces/create', [
                'name' => $name,
                'capacity' => $this->faker->numberBetween(1,5),
                'accommodations' => $this->faker->sentences(3, true),
                'notes' => $this->faker->sentences(2, true)
            ]);

        $response->assertStatus(422);

        $response->assertSee('A space with that name already exists for your group.');
    }

    /** @test */
    public function member_able_to_view_space_details()
    {
        $response = $this->actingAs($this->member, 'api')
            ->json('GET', '/api/spaces/' . $this->space->id);

        $response->assertOk();

        $response->assertSee(Space::find($this->space->id));
    }

    /** @test */
    public function reject_request_if_space_does_not_exist()
    {
        $response = $this->actingAs($this->member, 'api')
            ->json('GET', '/api/spaces/' . 1600);

        $response->assertStatus(404);

        $response->assertSee('Space not found.');
    }

    /** @test */
    public function reject_if_non_member_tries_to_access_space_details()
    {
        $response = $this->actingAs($this->nonMember, 'api')
            ->json('GET', '/api/spaces/' . $this->space->id);

        $response->assertStatus(401);

        $response->assertSee('Unauthorized access.');
    }

    /** @test */
    public function owner_able_to_edit_spaces()
    {
        $newName = $this->faker->secondaryAddress;
        $newAccommodations = $this->faker->sentences(2, true);
        $newNotes = $this->faker->sentences(3, true);

        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/spaces/' . $this->space->id . '/update', [
                'name' => $newName,
                'capacity' => $this->space->capacity + 1,
                'accommodations' => $newAccommodations,
                'notes' => $newNotes
            ]);

        $response->assertOk();

        $updatedSpace = Space::find($this->space->id);

        $this->assertTrue($updatedSpace->name == $newName);
        $this->assertTrue($updatedSpace->capacity == $this->space->capacity + 1);
        $this->assertTrue($updatedSpace->accommodations == $newAccommodations);
        $this->assertTrue($updatedSpace->notes == $newNotes);

        $response->assertSee('Space details updated.');
    }

    /** @test */
    public function reject_update_if_space_does_not_exist()
    {
        $newName = $this->faker->secondaryAddress;
        $newAccommodations = $this->faker->sentences(2, true);
        $newNotes = $this->faker->sentences(3, true);

        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/spaces/' . 1600 . '/update', [
                'name' => $newName,
                'capacity' => $this->space->capacity + 1,
                'accommodations' => $newAccommodations,
                'notes' => $newNotes
            ]);

        $response->assertStatus(404);

        $response->assertSee('Space not found.');
    }

    /** @test */
    public function reject_update_if_non_member()
    {
        $newName = $this->faker->secondaryAddress;
        $newAccommodations = $this->faker->sentences(2, true);
        $newNotes = $this->faker->sentences(3, true);

        $response = $this->actingAs($this->nonMember, 'api')
            ->json('POST', '/api/spaces/' . $this->space->id . '/update', [
                'name' => $newName,
                'capacity' => $this->space->capacity + 1,
                'accommodations' => $newAccommodations,
                'notes' => $newNotes
            ]);

        $response->assertStatus(401);

        $response->assertSee('Unauthorized access.');
    }

    /** @test */
    public function reject_update_with_empty_fields()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/spaces/' . $this->space->id . '/update', [
                'accommodations' => $this->faker->sentences(3, true),
                'notes' => $this->faker->sentences(2, true)
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function reject_update_with_invalid_fields()
    {
        $name = $this->faker->secondaryAddress;

        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/spaces/' . $this->space->id . '/update', [
                'name' => $name,
                'capacity' => 'jafa',
                'accommodations' => $this->faker->sentences(3, true),
                'notes' => $this->faker->sentences(2, true)
            ]);

        $response->assertStatus(422);
    }
    /** @test */
    public function reject_update_if_user_is_not_owner_of_group()
    {
        $newName = $this->faker->secondaryAddress;
        $newAccommodations = $this->faker->sentences(2, true);
        $newNotes = $this->faker->sentences(3, true);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/spaces/' . $this->space->id . '/update', [
                'name' => $newName,
                'capacity' => $this->space->capacity + 1,
                'accommodations' => $newAccommodations,
                'notes' => $newNotes
            ]);

        $response->assertStatus(401);

        $response->assertSee('Only group owners may alter space details.');
    }

    /** @test */
    public function owner_able_to_delete_spaces()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/spaces/' . $this->space->id . '/delete');

        $response->assertOk();
        $response->assertSee('Space deleted.');

        $this->assertTrue(! Space::find($this->space->id));
    }

    /** @test */
    public function reject_deletion_if_space_does_not_exist()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->json('POST', '/api/spaces/' . 1600 . '/delete');

        $response->assertStatus(404);
        $response->assertSee('Space not found.');

        $this->assertTrue(!! Space::find($this->space->id));
    }

    /** @test */
    public function reject_deletion_if_mismatched_groups()
    {
        $response = $this->actingAs($this->nonMember, 'api')
            ->json('POST', '/api/spaces/' . $this->space->id . '/delete');

        $response->assertStatus(401);
        $response->assertSee('Unauthorized access.');

        $this->assertTrue(!! Space::find($this->space->id));
    }

    /** @test */
    public function reject_deletion_if_user_is_not_owner()
    {
        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/spaces/' . $this->space->id . '/delete');

        $response->assertStatus(401);
        $response->assertSee('Only group owners may delete spaces.');

        $this->assertTrue(!! Space::find($this->space->id));
    }
}
