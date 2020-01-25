<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GroupController extends Controller
{
	public function create(Request $request) {

		// Validate the name field
		$this->validate($request, [
			'name' => 'required|max:40'
		]);

		// Check if user has a group already (users can only have one group)
		if ($request->user()->group) {
			return response('Users may only have one group.', 400);
		}

		// Check if group name already exists
		$existingGroup = \App\Group::where('name', $request->input('name'))->first();

		if ($existingGroup) {
			return response('That group name is unavailable.', 400);
		}

		// Create group with name from form entry, user id from $request->user
		// and set the user's group to the group ID of the newly-created group
		$newGroup = new \App\Group;
		$newGroup->name = $request->input('name');
		$newGroup->owner_id = $request->user()->id;
		//$newGroup->save();

		$request->user()->group_id = $newGroup->id;

		//$request->user()->save();

		return response('Group created successfully.', 200);
	}
}
