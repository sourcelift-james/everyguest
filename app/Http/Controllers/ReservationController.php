<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Return list of all reservations.
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        return Reservation::where('group_id', $request->user()->group->id)->get();
    }

    /**
     * Return a single reservation.
     * @param Request $request
     * @param int $reservation_id
     * @return Response
     */
    public function show(Request $request, int $reservation_id)
    {
        $reservation = Reservation::find($reservation_id);

        if (! $reservation) {
            return response('Reservation not found.', 404);
        }

        if ($reservation->group_id != $request->user()->group_id) {
            return response('You do not have access to that reservation.', 401);
        }

        return $reservation;
    }

    /**
     * Create a reservation.
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'guest_id' => 'required|int',
            'space_id' => 'required|int',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date'
        ]);

        /** Validate existence of related entities. */
        $guest = Guest::find($request->input('guest_id'));
        $space = Space::find($request->input('space_id'));

        if (! $guest) {
            return response('Guest not found.', 422);
        }

        if (! $space) {
            return response('Space not found.', 422);
        }

        /** Check to make sure Guest, Space, and User all belong to the same group. */
        if ($guest->group_id != $request->user()->group_id) {
            return response('That guest does not belong to your group.', 401);
        }

        if ($space->group_id != $request->user()->group_id) {
            return response('That space does not belong to your group.', 401);
        }

        /** Validate that no existing reservations exist for that space or guest. */
        $starts_at = Carbon\Carbon::createFromDate($request->input('starts_at'));
        $ends_at = Carbon\Carbon::createFromDate($request->input('ends_at'));

        $guestReservations = Reservation::where('guest_id', $guest->id)
            ->where([
                ['starts_at', '<=', $starts_at],
                ['ends_at', '>=', $starts_at]
            ])
            ->orWhere([
                ['starts_at', '<=', $ends_at],
                ['ends_at', '>=', $ends_at]
            ])
            ->orWhere('starts_at', $starts_at)
            ->orWhere('ends_at', $starts_at)
            ->orWhere('starts_at', $ends_at)
            ->orWhere('ends_at', $ends_at)
            ->get();

        if (!! $guestReservations->count()) {
            return response('That guest has already had a reservation for that time.', 422);
        }

        $spaceReservations = Reservation::where('space_id', $space->id)
            ->where([
                ['starts_at', '<=', $starts_at],
                ['ends_at', '>=', $starts_at]
            ])
            ->orWhere([
                ['starts_at', '<=', $ends_at],
                ['ends_at', '>=', $ends_at]
            ])
            ->orWhere('starts_at', $starts_at)
            ->orWhere('ends_at', $starts_at)
            ->orWhere('starts_at', $ends_at)
            ->orWhere('ends_at', $ends_at)
            ->get();

        if ($spaceReservations >= $space->capacity) {
            return response('That space has already reached its capacity for that time period.', 422);
        }

        Reservation::create([
            'guest_id' => $guest->id,
            'space_id' => $space->id,
            'group_id' => $request->user()->group_io,
            'starts_at' => $starts_at,
            'ends_at' => $ends_at,
            'notes' => $request->input('notes')
        ]);

        return response('Reservation created.', 200);
    }

    /**
     * Update reservation details.
     * @param Request $request
     * @param int $reservation_id
     * @return Response
     */
    public function update(Request $request, int $reservation_id)
    {
        $reservation = Reservation::find($reservation_id);

        if (! $reservation) {
            return response('Reservation not found.', 404);
        }

        $this->validate($request, [
            'guest_id' => 'required|int',
            'space_id' => 'required|int',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date'
        ]);

        /** Validate existence of related entities. */
        $guest = Guest::find($request->input('guest_id'));
        $space = Space::find($request->input('space_id'));

        if (! $guest) {
            return response('Guest not found.', 422);
        }

        if (! $space) {
            return response('Space not found.', 422);
        }

        /** Check to make sure Guest, Space, and User all belong to the same group. */
        if ($guest->group_id != $request->user()->group_id) {
            return response('That guest does not belong to your group.', 401);
        }

        if ($space->group_id != $request->user()->group_id) {
            return response('That space does not belong to your group.', 401);
        }

        /** Validate that no existing reservations exist for that space or guest. */
        $starts_at = Carbon\Carbon::createFromDate($request->input('starts_at'));
        $ends_at = Carbon\Carbon::createFromDate($request->input('ends_at'));

        $guestReservations = Reservation::where('guest_id', $guest->id)
            ->where([
                ['starts_at', '<=', $starts_at],
                ['ends_at', '>=', $starts_at]
            ])
            ->orWhere([
                ['starts_at', '<=', $ends_at],
                ['ends_at', '>=', $ends_at]
            ])
            ->orWhere('starts_at', $starts_at)
            ->orWhere('ends_at', $starts_at)
            ->orWhere('starts_at', $ends_at)
            ->orWhere('ends_at', $ends_at)
            ->where('id', '!=', $reservation->id)
            ->get();

        if (!! $guestReservations->count()) {
            return response('That guest has already had a reservation for that time.', 422);
        }

        $spaceReservations = Reservation::where('space_id', $space->id)
            ->where([
                ['starts_at', '<=', $starts_at],
                ['ends_at', '>=', $starts_at]
            ])
            ->orWhere([
                ['starts_at', '<=', $ends_at],
                ['ends_at', '>=', $ends_at]
            ])
            ->orWhere('starts_at', $starts_at)
            ->orWhere('ends_at', $starts_at)
            ->orWhere('starts_at', $ends_at)
            ->orWhere('ends_at', $ends_at)
            ->where('id', '!=', $reservation->id)
            ->get();

        if ($spaceReservations >= $space->capacity) {
            return response('That space has already reached its capacity for that time period.', 422);
        }

        $reservation->guest_id = $guest->id;
        $reservation->space_id = $space->id;
        $reservation->group_id = $request->user()->group_id;
        $reservation->starts_at = $starts_at;
        $reservation->ends_at = $ends_at;
        $reservation->notes = $request->input('notes');

        $reservation->save();

        return response('Reservation updated.', 200);
    }

    /**
     * Delete a reservation.
     * @param Request $request
     * @param int $reservation_id
     * @return Response
     */
    public function delete(Request $request, int $reservation_id)
    {
        $reservation = Reservation::find($reservation_id);

        if (! $reservation) {
            return response('Reservation not found.', 404);
        }

        if ($reservation->group_id != $request->user()->group_id) {
            return response('You do not have access to that reservation.', 401);
        }

        $reservation->delete();

        return response('Reservation deleted', 200);
    }
}
