<?php

namespace App\Http\Controllers;

use App\Models\User;
use http\Env\Response;
use Illuminate\Http\Request;
use \App\Models\Group;

class GroupController extends Controller
{
    /**
     * Create group.
     *
     * @param Request $request
     * @return Response
     */
	public function create(Request $request) {
		/** Validate name field. */
		$this->validate($request, [
			'name' => 'required|max:40'
		]);

		/** Validate if user has group already. */
		if ($request->user()->group) {
			return response('Users may only have one group.', 422);
		}

		/** Validate that group does not exist. */
		$existingGroup = Group::where('name', $request->input('name'))->first();

		if ($existingGroup) {
			return response('That group name is unavailable.', 422);
		}

		/** Create group with data from request. */
		$newGroup = Group::create([
			'name' => $request->input('name'),
			'owner_id' => $request->user()->id,
		]);

		$request->user()->group_id = $newGroup->id;

		$request->user()->save();

		return response('Group created successfully.', 200);
	}

    /**
     * Fetch group details.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
	public function show(Request $request, int $id) {
		$group = Group::find($id);

		if (!$group) {
			return response('No group found.', 422);
		}

		if ($group->id != $request->user()->id) {
			return response('User does not belong to group.', 422);
		}

		return $group;
	}

    /**
     * Fetch group members.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
	public function members(Request $request, int $id) {
		$group = Group::find($id);

		if (!$group) {
			return response ('No group found.', 422);
		}

		if ($group->id != $request->user()->group_id) {
			return response('User does not belong to group.', 422);
		}

		return User::where('group_id', $group->id)->get();
	}

    /**
     * Fetch specific group member details.
     * @param Request $request
     * @param int $group_id
     * @param int $member_id
     * @return Response
     */
	public function member(Request $request, int $group_id, int $member_id)
    {
        /** Make sure group exists. */
        $group = Group::find($group_id);

        if (! $group) {
            return response('No group found.', 422);
        }

        /** Make sure requesting user belongs to group. */
        if ($group->id != $request->user()->group_id) {
            return response('Logged in user does not belong to that group.', 422);
        }

        /** Make sure requested user exists. */
        $member = User::find($member_id);

        if (! $member) {
            return response('Selected member does not exist.', 422);
        }

        /** Make sure requested member belongs to group. */
        if ($member->group_id != $group->id) {
            return response('Selected member does not belong to selected group.', 422);
        }

        /** Return requested member. */
        return $member;
    }

    /**
     * Update group details.
     * @param Request $request
     * @param int $group_id
     * @return Response
     */
    public function update(Request $request, int $group_id)
    {
        /** Validate submission data. */
        $this->validate($request, [
            'name' => 'required|max:40',
            'owner' => 'required|integer'
        ]);

        /**
         * Find group.
         * @var Group $group
         */
        $group = Group::find($group_id);

        /** Reject if no group is found. */
        if (! $group) {
            return response('Group not found', 422);
        }

        /** Reject if user is not owner of group. */
        if ($group->owner_id != $request->user()->id) {
            return response('Unauthorized access.', 401);
        }

        /**
         * Find existing group with the new name, if exists.
         * @var $existingGroupByName
         */
        $existingGroupByName = Group::where('id', '!=', $group->id)->where('name', $request->input('name'))->first();

        /** If group exists, reject. */
        if (!! $existingGroupByName) {
            return response('That name is already taken by another group.', 422);
        }

        /**
         * Grab new owner for validation.
         * @var $prospectiveNewOwner
         */
        $prospectiveNewOwner = User::find($request->input('owner'));

        /** Reject if he doesn't exist. */
        if (! $prospectiveNewOwner) {
            return response('Selected new owner does not exist.', 422);
        }

        /** Reject if he's not in the current owner's group. */
        if ($prospectiveNewOwner->group_id != $group->id) {
            return response('Selected new owner does not belong to your group.', 422);
        }

        /**
         * Update group details.
         */
        $group->name = $request->input('name');
        $group->owner_id = $request->input('owner');

        $group->save();
    }

    /**
     * Invite new member to group.
     * @param Request $request
     * @param int $group_id
     * @return Response
     */
    public function invite(Request $request, int $group_id)
    {
        /** Validate input. */
        $this->validate($request, [
           'email' => 'required|email'
        ]);

        /** Make sure group exists. */
        $group = Group::find($group_id);

        if (! $group) {
            return response('Group does not exist.', 422);
        }

        /** Make sure user is owner of group. */
        if ($request->user()->id != $group->owner_id) {
            return response('Unauthorized access.', 401);
        }

        /** Find prospective member by email. */
        $prospectiveMember = User::where('email', $request->input('email'))->first();

        /** Make sure prospective member exists. */
        if (! $prospectiveMember) {
            return response('Invited user does not exist.', 422);
        }

        /** Make sure prospective member is not already in a group. */
        if (!! $prospectiveMember->group_id) {
            return response('Invited user is already in a group.', 422);
        }

        /** Update prospective member's group. */
        $prospectiveMember->group_id = $group->id;
        $prospectiveMember->save();

        /** Return successful response. */
        return response('New member added to group.', 200);
    }

    /**
     * Remove user from group.
     * @param Request $request
     * @param int $group_id
     * @param int $member_id
     * @return Response
     */
    public function dismissMember(Request $request, int $group_id, int $member_id)
    {
        /** Check if group exists. */
        $group = Group::find($group_id);

        if (! $group) {
            return response('Group does not exist.', 422);
        }

        /** Check if user is owner. */
        if ($group->owner_id != $request->user()->id) {
            return response('Unauthorized access.', 401);
        }

        /** Check if member exists. */
        $member = User::find($member_id);

        if (! $member) {
            return response('Member does not exist.', 422);
        }

        /** Check if member belongs to owner's group. */
        if ($member->group_id != $group->id) {
            return response('That user is not in your group.', 422);
        }

        /** Remove member from group. */
        $member->group_id = NULL;
        $member->save();

        return response('Member removed.', 200);
    }
}
