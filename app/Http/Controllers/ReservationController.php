<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

        /** Shed any time parameters that may have been added to the date. We're only using days. */
        $starts_at = Carbon::parse($request->input('starts_at'));
        $ends_at = Carbon::parse($request->input('ends_at'));

        if (! $starts_at) {
            return response('Your start date was invalid. Please try again.', 422);
        }

        if (! $ends_at) {
            return response('Your end date was invalid. Please try again', 422);
        }

        if ($ends_at->lessThan($starts_at)) {
            return response('Your reservation\'s end time must be after its start time.', 422);
        }

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

        $guestReservations = Reservation::where('guest_id', $guest->id)
            ->where(function($query) use ($starts_at, $ends_at) {
                /**
                 * If the submitted start time is between existing reservation times.
                 */
                $query->where([
                    ['starts_at', '<=', $starts_at],
                    ['ends_at', '>=', $starts_at]
                ])
                /**
                 * If the submitted end time is between existing reservation times.
                 */
                ->orWhere([
                    ['starts_at', '<=', $ends_at],
                    ['ends_at', '>=', $ends_at]
                ])
                /**
                 * If any existing reservation start times fall between the submitted start and end times.
                 */
                ->orWhereBetween('starts_at', [$starts_at, $ends_at ])
                /**
                 * If any existing reservation end times fall between the submitted start and end times.
                 */
                ->orWhereBetween('ends_at', [$starts_at, $ends_at ])
                /**
                 * If any starting or end times are exactly the same.
                 */
                ->orWhere('starts_at', $starts_at)
                ->orWhere('ends_at', $starts_at)
                ->orWhere('starts_at', $ends_at)
                ->orWhere('ends_at', $ends_at);
            })
            ->get();

        if (!! $guestReservations->count()) {
            return response('That guest already has a reservation for that time.', 422);
        }

        $spaceReservations = Reservation::where('space_id', $space->id)
            ->where(function($query) use ($starts_at, $ends_at) {
                /**
                 * If the submitted start time is between existing reservation times.
                 */
                $query->where([
                    ['starts_at', '<=', $starts_at],
                    ['ends_at', '>=', $starts_at]
                ])
                    /**
                     * If the submitted end time is between existing reservation times.
                     */
                    ->orWhere([
                        ['starts_at', '<=', $ends_at],
                        ['ends_at', '>=', $ends_at]
                    ])
                    /**
                     * If any existing reservation start times fall between the submitted start and end times.
                     */
                    ->orWhereBetween('starts_at', [$starts_at, $ends_at ])
                    /**
                     * If any existing reservation end times fall between the submitted start and end times.
                     */
                    ->orWhereBetween('ends_at', [$starts_at, $ends_at ])
                    /**
                     * If any starting or end times are exactly the same.
                     */
                    ->orWhere('starts_at', $starts_at)
                    ->orWhere('ends_at', $starts_at)
                    ->orWhere('starts_at', $ends_at)
                    ->orWhere('ends_at', $ends_at);
            })
            ->get();

        if ($spaceReservations->count() >= $space->capacity) {
            return response('That space has already reached its capacity for that time period.', 422);
        }

        Reservation::create([
            'guest_id' => $guest->id,
            'space_id' => $space->id,
            'group_id' => $request->user()->group_id,
            'starts_at' => $starts_at->toDateString(),
            'ends_at' => $ends_at->toDateString(),
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

        /** Shed any time parameters that may have been added to the date. We're only using days. */
        $starts_at = Carbon::parse($request->input('starts_at'));
        $ends_at = Carbon::parse($request->input('ends_at'));

        if (! $starts_at) {
            return response('Your start date was invalid. Please try again.', 422);
        }

        if (! $ends_at) {
            return response('Your end date was invalid. Please try again', 422);
        }

        if ($ends_at->lessThan($starts_at)) {
            return response('Your reservation\'s end time must be after its start time.', 422);
        }

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

        $guestReservations = Reservation::where('guest_id', $guest->id)
            ->where('id', '!=', $reservation->id)
            ->where(function($query) use ($starts_at, $ends_at) {
                /**
                 * If the submitted start time is between existing reservation times.
                 */
                $query->where([
                    ['starts_at', '<=', $starts_at],
                    ['ends_at', '>=', $starts_at]
                ])
                    /**
                     * If the submitted end time is between existing reservation times.
                     */
                    ->orWhere([
                        ['starts_at', '<=', $ends_at],
                        ['ends_at', '>=', $ends_at]
                    ])
                    /**
                     * If any existing reservation start times fall between the submitted start and end times.
                     */
                    ->orWhereBetween('starts_at', [$starts_at, $ends_at ])
                    /**
                     * If any existing reservation end times fall between the submitted start and end times.
                     */
                    ->orWhereBetween('ends_at', [$starts_at, $ends_at ])
                    /**
                     * If any starting or end times are exactly the same.
                     */
                    ->orWhere('starts_at', $starts_at)
                    ->orWhere('ends_at', $starts_at)
                    ->orWhere('starts_at', $ends_at)
                    ->orWhere('ends_at', $ends_at);
            })
            ->get();

        if (!! $guestReservations->count()) {
            return response('That guest already has a reservation for that time.', 422);
        }

        $spaceReservations = Reservation::where('space_id', $space->id)
            ->where('id', '!=', $reservation->id)
            ->where(function($query) use ($starts_at, $ends_at) {
                /**
                 * If the submitted start time is between existing reservation times.
                 */
                $query->where([
                    ['starts_at', '<=', $starts_at],
                    ['ends_at', '>=', $starts_at]
                ])
                    /**
                     * If the submitted end time is between existing reservation times.
                     */
                    ->orWhere([
                        ['starts_at', '<=', $ends_at],
                        ['ends_at', '>=', $ends_at]
                    ])
                    /**
                     * If any existing reservation start times fall between the submitted start and end times.
                     */
                    ->orWhereBetween('starts_at', [$starts_at, $ends_at ])
                    /**
                     * If any existing reservation end times fall between the submitted start and end times.
                     */
                    ->orWhereBetween('ends_at', [$starts_at, $ends_at ])
                    /**
                     * If any starting or end times are exactly the same.
                     */
                    ->orWhere('starts_at', $starts_at)
                    ->orWhere('ends_at', $starts_at)
                    ->orWhere('starts_at', $ends_at)
                    ->orWhere('ends_at', $ends_at);
            })
            ->get();

        if ($spaceReservations->count() >= $space->capacity) {
            return response('That space has already reached its capacity for that time period.', 422);
        }

        $reservation->guest_id = $guest->id;
        $reservation->space_id = $space->id;
        $reservation->group_id = $request->user()->group_id;
        $reservation->starts_at = $starts_at->toDateString();
        $reservation->ends_at = $ends_at->toDateString();
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

        return response('Reservation deleted.', 200);
    }
}
