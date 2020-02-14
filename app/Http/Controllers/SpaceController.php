<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Space;
use Illuminate\Http\Request;

class SpaceController extends Controller
{
    /**
     * Return list of spaces.
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        return Space::where('group_id', $request->user()->group_id)->get();
    }

    /**
     * Create a space to be reserved by a guest.
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        /** Validate space fields. */
        $this->validate($request, [
            'name' => 'required|max:40',
            'capacity' => 'required|integer'
        ]);

        /** Make sure user belongs to group. */
        if (! $request->user()->group_id) {
            return response('You must belong to a group to create a space.', 401);
        }

        $group = Group::find($request->user()->group_id);

        if (! $group) {
            return response('You must belong to a group to create a space.', 401);
        }

        /** Make sure a space for this group does not already have that name. */
        $existingSpace = Space::where('group_id', $group->id)->where('name', $request->input('name'))->first();

        if (!! $existingSpace) {
            return response('A space with that name already exists for your group.', 422);
        }

        /** Make sure user is owner of group. */
        if ($group->owner_id != $request->user()->id) {
            return response('Only group owners can create and manage spaces.', 401);
        }

        /** Create space. */
        Space::create([
            'group_id' => $group->id,
            'name' => $request->input('name'),
            'capacity' => $request->input('capacity'),
            'accommodations' => $request->input('accommodations'),
            'notes' => $request->input('notes')
        ]);

        return response('Space created.', 200);
    }

    /**
     * Fetch space details.
     * @param Request $request
     * @param Space $space
     * @return Response
     */
    public function show(Request $request, Space $space)
    {

        /** Reject if user's group does not match space's. */
        if ($space->group_id != $request->user()->group_id) {
            return response('Unauthorized access.', 401);
        }

        /** Return space details. */
        return $space;
    }

    /**
     * Update space details.
     * @param Request $request
     * @param Space $space
     * @return Response
     */
    public function update(Request $request, Space $space)
    {
        /** Validate form input. */
        $this->validate($request, [
            'name' => 'required|max:40',
            'capacity' => 'required|integer'
        ]);

        /** Reject if user's group does not match space's. */
        if ($request->user()->group_id != $space->group_id) {
            return response('Unauthorized access.', 401);
        }

        /** Reject if user is not owner of group. */
        if ($space->group()->first()->owner_id != $request->user()->id) {
            return response('Only group owners may alter space details.', 401);
        }

        /** Update space details. */
        $space->name = $request->input('name');
        $space->capacity = $request->input('capacity');
        $space->accommodations = $request->input('accommodations');
        $space->notes = $request->input('notes');

        $space->save();

        return response('Space details updated.', 200);
    }

    /**
     * Delete space.
     * @param Request $request
     * @param int $space_id
     * @return Response
     */
    public function delete(Request $request, Space $space)
    {

        /** Reject if user's group does not match space's. */
        if ($request->user()->group_id != $space->group_id) {
            return response('Unauthorized access.', 401);
        }

        /** Reject if user is not owner of group. */
        if ($space->group()->first()->owner_id != $request->user()->id) {
            return response('Only group owners may delete spaces.', 401);
        }

        /** Delete space. */
        $space->delete();

        return response('Space deleted.', 200);
    }
}
