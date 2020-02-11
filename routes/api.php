<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([ 'prefix' => 'auth'], function (){
    Route::group(['middleware' => ['guest:api']], function () {
        Route::post('login', 'API\AuthController@login');
        Route::post('signup', 'API\AuthController@signup');
    });
    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('logout', 'API\AuthController@logout');
        Route::get('getuser', 'API\AuthController@getUser');
    });
});

Route::group(['middleware' => ['auth:api']], function() {

	/** Group Routes */

    /**
     * Create group.
     * TESTED.
     */
	Route::post('group/create', 'GroupController@create');

	/**
     * Fetch group details.
     * TESTED.
     */
	Route::get('group/{id}', 'GroupController@show')
        ->where('id', '[0-9]+');

    /**
     * Fetch group members.
     * TESTED.
     */
	Route::get('group/{id}/members', 'GroupController@members')
        ->where('id', '[0-9]+');

    /**
     * Fetch group member details.
     * TESTED.
     */
    Route::get('group/{group_id}/members/{member_id}', 'GroupController@member')
        ->where('group_id', '[0-9]+')
        ->where('member_id', '[0-9]+');

	/**
     * Update group details.
     * TESTED.
     */
	Route::post('group/{id}/update', 'GroupController@update')
        ->where('id', '[0-9]+');

    /**
     * View list of group members.
     */
    Route::get('group/{group_id}/members', 'GroupController@index')
        ->where('group_id', '[0-9]+');

	/**
     * Invite new member.
     * TESTED.
     */
	Route::post('group/{id}/invite', 'GroupController@invite')
	    ->where('id', '[0-9]+');

	/**
     * Remove existing member.
     * TESTED.
     */
	Route::post('group/{group_id}/members/{member_id}/remove', 'GroupController@dismissMember')
        ->where('group_id', '[0-9]+')
	    ->where('member_id', '[0-9]+');


	/** Space Routes */

    /**
     * View list of spaces.
     */
    Route::get('spaces', 'SpaceController@index');

    /**
     * Create a space to be reserved by a guest.
     * TESTED.
     */
    Route::post('spaces/create', 'SpaceController@create');

    /**
     * Fetch space details.
     * TESTED.
     */
    Route::get('spaces/{space_id}', 'SpaceController@show')
        ->where('space_id', '[0-9]+');

    /**
     * Update space details.
     * TESTED.
     */
    Route::post('spaces/{space_id}/update', 'SpaceController@update')
        ->where('space_id', '[0-9]+');

    /**
     * Delete space.
     * TESTED.
     */
    Route::post('spaces/{space_id}/delete', 'SpaceController@delete')
        ->where('space_id', '[0-9]+');

    /** Invitation Routes */

    /**
     * Get all invitations.
     * WRITTEN.
     */
    Route::get('invitations', 'InvitationController@index');

    /**
     * Create an invitation.
     * WRITTEN.
     */
    Route::post('invitations/create', 'InvitationController@create');

    /**
     * View invitation properties and fields.
     * WRITTEN.
     */
    Route::get('invitations/{invitation_id}', 'InvitationController@show')
        ->where('invitation_id', '[0-9]+');

    /**
     * Update invitation properties.
     * WRITTEN.
     */
    Route::post('invitations/{invitation_id}/update', 'InvitationController@update')
        ->where('invitation_id', '[0-9]+');

    /**
     * Delete invitation.
     * WRITTEN.
     */
    Route::post('invitations/{invitation_id}', 'InvitationController@delete')
        ->where('invitation_id', '[0-9]+');

    /**
     * Submitted invitation form for new guests.
     * WRITTEN.
     */
    Route::get('welcome/{invitation_token}', 'InvitationController@display');

    /**
     * Submitted invitation form for new guests.
     * WRITTEN.
     */
    Route::post('welcome/{invitation_token}', 'InvitationController@submit');

});
