<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{

    /**
     * List your group's guests.
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        return Guest::where('group_id', $request->user()->group_id);
    }

    /**
     * Show a specific guest's details.
     * @param Request $request
     * @param Guest $guest
     * @return Response
     */
    public function show(Request $request, Guest $guest)
    {
        /** Reject if user is not the same group as guest. */
        if ($guest->group_id != $request->user()->group_id) {
            return response('Unauthorized access.', 401);
        }

        return $guest;
    }

    /**
     * Create a guest.
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        /** Validate the same inputs we would use if the guest was submitting the data themselves. */
        $this->validate($request, [
            'first' => 'required',
            'last' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'arrivalMethod' => 'required',
            'arrivalTime' => 'required',
            'departureMethod' => 'required',
            'departureTime' => 'required',
        ]);

        Guest::create([
            'group_id' => $request->user()->group_id,
            'first' => $request->input('first'),
            'last' => $request->input('last'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'address' => $request->input('address'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'zip' => $request->input('zip'),
            'arrivalMethod' => $request->input('arrivalMethod'),
            'arrivalTime' => $request->input('arrivalTime'),
            'departureMethod' => $request->input('departureMethod'),
            'departureTime' => $request->input('departureTime'),
            'notes' => $request->input('notes')
        ]);

        return response('Guest created.', 200);
    }

    /**
     * Update a guest.
     * @param Request $request
     * @param Guest $guest
     * @return Response
     */
    public function update(Request $request, Guest $guest)
    {
        /** Ensure guest has same group has user. */
        if ($guest->group_id != $request->user()->group_id) {
            return response('Unauthorized access.', 401);
        }

        /** Validate the same inputs we would use if the guest was submitting the data themselves. */
        $this->validate($request, [
            'first' => 'required',
            'last' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'arrivalMethod' => 'required',
            'arrivalTime' => 'required',
            'departureMethod' => 'required',
            'departureTime' => 'required',
        ]);

        $guest->first = $request->input('first');
        $guest->last = $request->input('last');
        $guest->phone = $request->input('phone');
        $guest->email = $request->input('email');
        $guest->address = $request->input('address');
        $guest->city = $request->input('city');
        $guest->state = $request->input('state');
        $guest->zip = $request->input('zip');
        $guest->arrivalMethod = $request->input('arrivalMethod');
        $guest->arrivalTime = $request->input('arrivalTime');
        $guest->departureMethod = $request->input('departureMethod');
        $guest->departureTime = $request->input('departureTime');
        $guest->notes = $request->input('notes');

        $guest->save();

        return response('Guest created.', 200);

    }

    /**
     * Delete a guest.
     * @param Request $request
     * @param Guest $guest
     * @return Response
     */
    public function delete(Request $request, Guest $guest)
    {

        /** Ensure guest has same group has user. */
        if ($guest->group_id != $request->user()->group_id) {
            return response('Unauthorized access.', 401);
        }

        $guest->delete();

        return response('Guest deleted.', 200);
    }
}
