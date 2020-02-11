<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InvitationController extends Controller
{

    /**
     * Get all invitations.
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        /** Check if user belongs to a group. */
        if (! $request->user()->group_id) {
            return response('You must join or create a group to create invitations.', 401);
        }

        return Invitation::where('group_id', $request->user()->group_id)->get();
    }

    /**
     * Create an invitation.
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        /** Check if user belongs to a group. */
        if (! $request->user()->group_id) {
            return response('You must join or create a group to create invitations.', 401);
        }

        /** Validate name input. */
        $this->validate($request, [
           'name' => 'required',
            'expiration' => 'date',
        ]);

        $invitation = Invitation::create([
            'group_id' => $request->user()->group_id,
            'name' => $request->input('name'),
            'creator_id' => $request->user()->id,
            'expired_at' => $request->input('expiration'),
        ]);

        return response('Invitation created.', 200);
    }

    /**
     * View invitation properties and fields.
     * @param Request $request
     * @param int $invitation_id
     * @return Response
     */
    public function show(Request $request, int $invitation_id)
    {
        /** Check if user belongs to a group. */
        if (! $request->user()->group_id) {
            return response('You must join or create a group to create invitations.', 401);
        }

        /** Make sure invitation exists. */
        $invitation = Invitation::find($invitation_id);

        if (! $invitation) {
            return response('Invitation not found.', 404);
        }

        return $invitation;
    }

    /**
     * Update invitation properties.
     * @param Request $request
     * @param int $invitation_id
     * @return Response
     */
    public function update(Request $request, int $invitation_id)
    {
        /** Check if user belongs to a group. */
        if (! $request->user()->group_id) {
            return response('You must join or create a group to create invitations.', 401);
        }

        /** Make sure invitation exists. */
        $invitation = Invitation::find($invitation_id);

        if (! $invitation) {
            return response('Invitation not found.', 404);
        }

        $submittedDetails = $request->input('details');

        $invitationDetails = [];

        foreach ($submittedDetails as $detail) {
            $name = preg_replace('@[^a-z0-9-]+@','-', strtolower($detail['label']));
            $invitationDetails[$name] = [
                'label' => $detail['label'],
                'type' => $detail['type'],
                'validation' => ($detail['required']) ? 'required' : ''
            ];
        }

        $invitation->details = $invitationDetails;

        $invitation->save();
    }

    /**
     * Delete an invitation.
     * @param Request $request
     * @param int $invitation_id
     * @return Response
     */
    public function delete(Request $request, int $invitation_id)
    {
        /** Check if user belongs to a group. */
        if (! $request->user()->group_id) {
            return response('You must join or create a group to create invitations.', 401);
        }

        /** Make sure invitation exists. */
        $invitation = Invitation::find($invitation_id);

        if (! $invitation) {
            return response('Invitation not found.', 404);
        }

        $invitation->delete();

        return response('Invitation deleted.', 200);

    }

    /**
     * Display invitation information for prospective guests.
     * @param Request $request
     * @param string $invitation_token
     * @return Invitation $invitation
     */
    public function display(Request $request, string $invitation_token)
    {
        /** Make sure invitation exists. */
        $invitation = Invitation::where('token', $invitation_token)->first;

        if (! $invitation) {
            return response('Invitation not found.', 404);
        }

        /** Make sure invitation has not expired. */
        if ($invitation->expired_at && $invitation->expired_at->lt(Carbon::now())) {
            return response('That invitation has expired.', 404);
        }

        return $invitation;
    }

    /**
     * Guest submits an invitation form.
     * @param Request $request
     * @param string $invitation_token
     * @return Response
     */
    public function submit(Request $request, string $invitation_token)
    {
        /** Make sure invitation exists. */
        $invitation = Invitation::where('token', $invitation_token)->first();

        if (! $invitation) {
            return response('Invitation not found.', 404);
        }

        /** Make sure invitation has not expired. */
        if ($invitation->expired_at && $invitation->expired_at->lt(Carbon::now())) {
            return response('That invitation has expired.', 404);
        }

        /** Validate form inputs (quite the challenge) */
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

        /** Loop custom details created for the invitation for validation and collection. */
        $details = [];

        if ($invitation->details) {

            foreach ($invitation->details as $detail) {

                $this->validate($request, [
                   $detail['name'] => $detail['validation']
                ]);

                $details[$detail['name']] = [
                  'label' => $detail['label'],
                  'value' => $request->input($detail['name'])
                ];
            }
        }

        /** Start building the guest object with all the base and added details */
        $baseKeys = Guest::baseKeys;

        $guestDetails = $request->only($baseKeys);

        $guestDetails['group_id'] = $invitation->group_id;
        $guestDetails['invitation_id'] = $invitation->id;

        $guestDetails['details'] = $details;

        Guest::create($guestDetails);

        return response('Thank you for submitting your information.', 200);
    }
}
