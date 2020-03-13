<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Group;
use App\Models\Guest;
use App\Models\Space;
use App\Models\Reservation;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationREST extends TestCase
{
    use RefreshDatabase;

    private $group;
    private $member;
    private $nonMember;
    private $guest;
    private $space;

    public function setUp(): void
    {
        parent::setUp();
        $this->group = factory(Group::class)->create();

        $this->member = factory(User::class)->create([
            'group_id' => $this->group->id
        ]);

        $this->nonMember = factory(User::class)->create();

        $this->space = factory(Space::class)->create([
            'group_id' => $this->group->id,
            'capacity' => 1
        ]);

        $this->guest = factory(Guest::class)->create([
            'group_id' => $this->group->id
        ]);
    }

    /** @test */
    public function list_reservations()
    {
        /** Create reservations for the user to see. */
        factory(Reservation::class, 5)->create([
            'group_id' => $this->group->id
        ]);

        /** This route has no validation, it just needs to work. */
        $response = $this->actingAs($this->member, 'api')
            ->json('GET', '/api/reservations');

        $response->assertOk();

        $response->assertSee(Reservation::where('group_id', $this->group->id)->get());
    }

    /** @test */
    public function list_empty_reservations()
    {
        /** Make sure when no reservations are found, an empty array is returned. */
        $response = $this->actingAs($this->member, 'api')
            ->json('GET', '/api/reservations');

        $response->assertOk();

        $this->assertTrue(Reservation::where('group_id', $this->group->id)->get()->count() == 0);

        $response->assertSee(Reservation::where('group_id', $this->group->id)->get());
    }

    /** @test */
    public function view_single_reservation()
    {
        /** Create reservation for the user to find. */
        $reservation = factory(Reservation::class)->create([
            'group_id' => $this->group->id
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('GET', '/api/reservations/' . $reservation->id);

        $response->assertOk();

        $response->assertSee(Reservation::find($reservation->id));
    }

    /** @test */
    public function view_missing_reservation()
    {
        $response = $this->actingAs($this->member, 'api')
            ->json('GET', '/api/reservations/' . 1600);

        $response->assertStatus(404);
    }

    /** @test */
    public function view_reservation_for_another_group()
    {
        $otherGroup = factory(Group::class)->create();

        /** Create reservation for the user to "find". */
        $reservation = factory(Reservation::class)->create([
            'group_id' => $otherGroup->id
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('GET', '/api/reservations/' . $reservation->id);

        $response->assertStatus(401);

        $response->assertSee('Unauthorized access.');
    }

    /** @test */
    public function create_reservation()
    {
        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/create', [
                'guest_id' => $this->guest->id,
                'space_id' => $this->space->id,
                'starts_at' => '2020-02-01',
                'ends_at' => '2020-02-04',
            ]);

        $response->assertOk();

        $response->assertSee('Reservation created.');
    }

    /** @test */
    public function create_reservation_with_higher_capacity_space()
    {
        /** Create new space with higher capacity to test. */
        $this->space = factory(Space::class)->create([
            'group_id' => $this->group->id,
            'capacity' => 2
        ]);

        factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-01-20 12:00:00',
            'ends_at' => '2020-02-05 12:00:00'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/create', [
                'guest_id' => $this->guest->id,
                'space_id' => $this->space->id,
                'starts_at' => '2020-02-01 12:00:00',
                'ends_at' => '2020-02-04 12:00:00',
            ]);

        $response->assertOk();

        $response->assertSee('Reservation created.');

    }

    /** @test */
    public function create_reservation_with_missing_guest()
    {
        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/create', [
                'space_id' => $this->space->id,
                'starts_at' => '2020-02-01 12:00:00',
                'ends_at' => '2020-02-04 12:00:00',
            ]);

        /** Fail validation. */
        $response->assertStatus(422);
    }

    /** @test */
    public function create_reservation_with_missing_space()
    {
        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/create', [
                'guest_id' => $this->guest->id,
                'starts_at' => '2020-02-01 12:00:00',
                'ends_at' => '2020-02-04 12:00:00',
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function create_reservation_with_missing_start_time()
    {
        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/create', [
                'guest_id' => $this->guest->id,
                'space_id' => $this->space->id,
                'ends_at' => '2020-02-04 12:00:00',
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function create_reservation_with_missing_end_time()
    {
        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/create', [
                'guest_id' => $this->guest->id,
                'space_id' => $this->space->id,
                'starts_at' => '2020-02-01 12:00:00',
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function create_reservation_with_nonexistent_guest()
    {
        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/create', [
                'guest_id' => 1600,
                'space_id' => $this->space->id,
                'starts_at' => '2020-02-01 12:00:00',
                'ends_at' => '2020-02-04 12:00:00',
            ]);

        $response->assertStatus(422);

        $response->assertSee('Guest not found.');
    }

    /** @test */
    public function create_reservation_with_nonexistent_space()
    {
        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/create', [
                'guest_id' => $this->guest->id,
                'space_id' => 1600,
                'starts_at' => '2020-02-01 12:00:00',
                'ends_at' => '2020-02-04 12:00:00',
            ]);

        $response->assertStatus(422);

        $response->assertSee('Space not found.');
    }

    /** @test */
    public function create_reservation_with_guest_from_another_group()
    {
        $otherGroup = factory(Group::class)->create();
        $otherGuest = factory(Guest::class)->create([
            'group_id' => $otherGroup->id
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/create', [
                'guest_id' => $otherGuest->id,
                'space_id' => $this->space->id,
                'starts_at' => '2020-02-01 12:00:00',
                'ends_at' => '2020-02-04 12:00:00',
            ]);

        $response->assertStatus(401);

        $response->assertSee('That guest does not belong to your group.');
    }

    /** @test */
    public function create_reservation_with_space_from_another_group()
    {
        $otherGroup = factory(Group::class)->create();
        $otherSpace = factory(Space::class)->create([
            'group_id' => $otherGroup->id
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/create', [
                'guest_id' => $this->guest->id,
                'space_id' => $otherSpace->id,
                'starts_at' => '2020-02-01 12:00:00',
                'ends_at' => '2020-02-04 12:00:00',
            ]);

        $response->assertStatus(401);

        $response->assertSee('That space does not belong to your group.');
    }

    /** @test */
    public function create_reservation_with_overlapping_start_time_with_same_guest()
    {
        factory(Reservation::class)->create([
           'group_id' => $this->group->id,
           'guest_id' => $this->guest->id,
           'starts_at' => '2020-01-30 12:00:00',
           'ends_at' => '2020-02-03 12:00:00'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/create', [
                'guest_id' => $this->guest->id,
                'space_id' => $this->space->id,
                'starts_at' => '2020-02-01 12:00:00',
                'ends_at' => '2020-02-04 12:00:00',
            ]);

        $response->assertStatus(422);

        $response->assertSee('That guest already has a reservation for that time.');
    }

    /** @test */
    public function create_reservation_with_overlapping_end_time_with_same_guest()
    {
        factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'starts_at' => '2020-02-03 12:00:00',
            'ends_at' => '2020-02-06 12:00:00'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/create', [
                'guest_id' => $this->guest->id,
                'space_id' => $this->space->id,
                'starts_at' => '2020-02-01 12:00:00',
                'ends_at' => '2020-02-04 12:00:00',
            ]);

        $response->assertStatus(422);

        $response->assertSee('That guest already has a reservation for that time.');
    }

    /** @test */
    public function create_reservation_with_overlapping_start_time_with_same_space()
    {
        factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-01-30 12:00:00',
            'ends_at' => '2020-02-03 12:00:00'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/create', [
                'guest_id' => $this->guest->id,
                'space_id' => $this->space->id,
                'starts_at' => '2020-02-01 12:00:00',
                'ends_at' => '2020-02-04 12:00:00',
            ]);

        $response->assertStatus(422);

        $response->assertSee('That space has already reached its capacity for that time period.');
    }

    /** @test */
    public function create_reservation_with_overlapping_end_time_with_same_space()
    {
        factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-03 12:00:00',
            'ends_at' => '2020-02-06 12:00:00'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/create', [
                'guest_id' => $this->guest->id,
                'space_id' => $this->space->id,
                'starts_at' => '2020-02-01 12:00:00',
                'ends_at' => '2020-02-04 12:00:00',
            ]);

        $response->assertStatus(422);

        $response->assertSee('That space has already reached its capacity for that time period.');
    }

    /** @test */
    public function create_reservation_with_start_time_after_end_time()
    {
        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/create', [
                'guest_id' => $this->guest->id,
                'space_id' => $this->space->id,
                'ends_at' => '2020-02-01 12:00:00',
                'starts_at' => '2020-02-04 12:00:00',
            ]);

        $response->assertStatus(422);

        $response->assertSee('Your reservation\'s end time must be after its start time.');
    }

    /** @test */
    public function update_reservation()
    {
        $reservation = factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-10',
            'ends_at' => '2020-02-15'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . $reservation->id . '/update', [
                'guest_id' => $reservation->guest_id,
                'space_id' => $reservation->space_id,
                'starts_at' => '2020-02-12',
                'ends_at' => '2020-02-15'
            ]);

        $response->assertOk();

        $response->assertSee('Reservation updated.');
    }

    /** @test */
    public function update_reservation_with_missing_guest()
    {
        $reservation = factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-10',
            'ends_at' => '2020-02-15'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . $reservation->id . '/update', [
                'space_id' => $reservation->space_id,
                'starts_at' => '2020-02-12',
                'ends_at' => '2020-02-15'
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function update_reservation_with_missing_space()
    {
        $reservation = factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-10',
            'ends_at' => '2020-02-15'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . $reservation->id . '/update', [
                'guest_id' => $reservation->guest_id,
                'starts_at' => '2020-02-12',
                'ends_at' => '2020-02-15'
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function update_reservation_with_missing_start_time()
    {
        $reservation = factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-10',
            'ends_at' => '2020-02-15'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . $reservation->id . '/update', [
                'guest_id' => $reservation->guest_id,
                'space_id' => $reservation->space_id,
                'ends_at' => '2020-02-15'
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function update_reservation_with_missing_end_time()
    {
        $reservation = factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-10',
            'ends_at' => '2020-02-15'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . $reservation->id . '/update', [
                'guest_id' => $reservation->guest_id,
                'space_id' => $reservation->space_id,
                'starts_at' => '2020-02-12'
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function update_reservation_with_nonexistent_guest()
    {
        $reservation = factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-10',
            'ends_at' => '2020-02-15'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . $reservation->id . '/update', [
                'guest_id' => 1600,
                'space_id' => $reservation->space_id,
                'starts_at' => '2020-02-12',
                'ends_at' => '2020-02-15'
            ]);

        $response->assertStatus(422);

        $response->assertSee('Guest not found.');
    }

    /** @test */
    public function update_reservation_with_nonexistent_space()
    {
        $reservation = factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-10',
            'ends_at' => '2020-02-15'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . $reservation->id . '/update', [
                'guest_id' => $reservation->guest_id,
                'space_id' => 1600,
                'starts_at' => '2020-02-12',
                'ends_at' => '2020-02-15'
            ]);

        $response->assertStatus(422);

        $response->assertSee('Space not found.');
    }

    /** @test */
    public function update_reservation_with_guest_from_another_group()
    {
        $otherGroup = factory(Group::class)->create();
        $otherGuest = factory(Guest::class)->create([
            'group_id' => $otherGroup->id
        ]);

        $reservation = factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-10',
            'ends_at' => '2020-02-15'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . $reservation->id . '/update', [
                'guest_id' => $otherGuest->id,
                'space_id' => $reservation->space_id,
                'starts_at' => '2020-02-12',
                'ends_at' => '2020-02-15'
            ]);

        $response->assertStatus(401);

        $response->assertSee('That guest does not belong to your group.');
    }

    /** @test */
    public function update_reservation_with_space_from_another_group()
    {
        $otherGroup = factory(Group::class)->create();
        $otherSpace = factory(Space::class)->create([
            'group_id' => $otherGroup->id
        ]);

        $reservation = factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-10',
            'ends_at' => '2020-02-15'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . $reservation->id . '/update', [
                'guest_id' => $reservation->guest_id,
                'space_id' => $otherSpace->id,
                'starts_at' => '2020-02-12',
                'ends_at' => '2020-02-15'
            ]);

        $response->assertStatus(401);

        $response->assertSee('That space does not belong to your group.');
    }

    /** @test */
    public function update_reservation_with_overlapping_start_time_with_same_guest()
    {
        factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'starts_at' => '2020-02-05',
            'ends_at' => '2020-02-09'
        ]);

        $updatedReservation = factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-10',
            'ends_at' => '2020-02-15'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . $updatedReservation->id . '/update', [
                'guest_id' => $updatedReservation->guest_id,
                'space_id' => $updatedReservation->space_id,
                'starts_at' => '2020-02-05',
                'ends_at' => '2020-02-15'
            ]);

        $response->assertStatus(422);

        $response->assertSee('That guest already has a reservation for that time.');
    }

    /** @test */
    public function update_reservation_with_overlapping_end_time_with_same_guest()
    {
        factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'starts_at' => '2020-02-16',
            'ends_at' => '2020-02-25'
        ]);

        $updatedReservation = factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-10',
            'ends_at' => '2020-02-15'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . $updatedReservation->id . '/update', [
                'guest_id' => $updatedReservation->guest_id,
                'space_id' => $updatedReservation->space_id,
                'starts_at' => '2020-02-10',
                'ends_at' => '2020-02-19'
            ]);

        $response->assertStatus(422);

        $response->assertSee('That guest already has a reservation for that time.');
    }

    /** @test */
    public function update_reservation_with_overlapping_start_time_with_same_space()
    {
        factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-05',
            'ends_at' => '2020-02-09'
        ]);

        $updatedReservation = factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-10',
            'ends_at' => '2020-02-15'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . $updatedReservation->id . '/update', [
                'guest_id' => $updatedReservation->guest_id,
                'space_id' => $updatedReservation->space_id,
                'starts_at' => '2020-02-07',
                'ends_at' => '2020-02-15'
            ]);

        $response->assertStatus(422);

        $response->assertSee('That space has already reached its capacity for that time period.');
    }

    /** @test */
    public function update_reservation_with_overlapping_end_time_with_same_space()
    {
        factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-17',
            'ends_at' => '2020-02-25'
        ]);

        $updatedReservation = factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-10',
            'ends_at' => '2020-02-15'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . $updatedReservation->id . '/update', [
                'guest_id' => $updatedReservation->guest_id,
                'space_id' => $updatedReservation->space_id,
                'starts_at' => '2020-02-10',
                'ends_at' => '2020-02-19'
            ]);

        $response->assertStatus(422);

        $response->assertSee('That space has already reached its capacity for that time period.');
    }

    /** @test */
    public function update_reservation_with_start_time_after_end_time()
    {
        $updatedReservation = factory(Reservation::class)->create([
            'group_id' => $this->group->id,
            'guest_id' => $this->guest->id,
            'space_id' => $this->space->id,
            'starts_at' => '2020-02-10',
            'ends_at' => '2020-02-15'
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . $updatedReservation->id . '/update', [
                'guest_id' => $updatedReservation->guest_id,
                'space_id' => $updatedReservation->space_id,
                'starts_at' => '2020-02-15',
                'ends_at' => '2020-02-07'
            ]);

        $response->assertStatus(422);

        $response->assertSee('Your reservation\'s end time must be after its start time.');
    }

    /** @test */
    public function delete_reservation()
    {
        $reservation = factory(Reservation::class)->create([
           'group_id' => $this->group->id
        ]);

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . $reservation->id . '/delete');

        $response->assertOk();

        $response->assertSee('Reservation deleted.');
    }

    /** @test */
    public function delete_reservation_with_nonexistent_reservation()
    {
        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . 1600 . '/delete');

        $response->assertStatus(404);
    }

    /** @test */
    public function delete_reservation_from_another_group()
    {
        $reservation = factory(Reservation::class)->create();

        $response = $this->actingAs($this->member, 'api')
            ->json('POST', '/api/reservations/' . $reservation->id . '/delete');

        $response->assertStatus(401);

        $response->assertSee('You do not have access to that reservation.');
    }
}
