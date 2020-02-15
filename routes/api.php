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
	Route::get('group/{group}', 'GroupController@show');

    /**
     * Fetch group members.
     * TESTED.
     */
	Route::get('group/{group}/members', 'GroupController@members');

    /**
     * Fetch group member details.
     * TESTED.
     */
    Route::get('group/{group}/members/{user}', 'GroupController@member');

	/**
     * Update group details.
     * TESTED.
     */
	Route::post('group/{group}/update', 'GroupController@update');

	/**
     * Invite new member.
     * TESTED.
     */
	Route::post('group/{group}/invite', 'GroupController@invite');

	/**
     * Remove existing member.
     * TESTED.
     */
	Route::post('group/{group}/members/{user}/remove', 'GroupController@remove')
        ->where('group_id', '[0-9]+')
	    ->where('member_id', '[0-9]+');


	/** Space Routes */

    /**
     * View list of spaces.
     * TESTED.
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
    Route::get('spaces/{space}', 'SpaceController@show');

    /**
     * Update space details.
     * TESTED.
     */
    Route::post('spaces/{space}/update', 'SpaceController@update');

    /**
     * Delete space.
     * TESTED.
     */
    Route::post('spaces/{space}/delete', 'SpaceController@delete');

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
    Route::get('invitations/{invitation}', 'InvitationController@show');

    /**
     * Update invitation properties.
     * WRITTEN.
     */
    Route::post('invitations/{invitation}/update', 'InvitationController@update');

    /**
     * Delete invitation.
     * WRITTEN.
     */
    Route::post('invitations/{invitation}', 'InvitationController@delete');

    /** Reservation Routes */

    /**
     * List reservations.
     * TESTED.
     */
    Route::get('reservations', 'ReservationController@index');

    /**
     * View a single reservation.
     * TESTED.
     */
    Route::get('reservations/{reservation}', 'ReservationController@show');

    /**
     * Create a reservation.
     * TESTED.
     */
    Route::post('reservations/create', 'ReservationController@create');

    /**
     * Update a reservation.
     * TESTED.
     */
    Route::post('reservations/{reservation}/update', 'ReservationController@update');

    /**
     * Delete a reservation.
     * TESTED.
     */
    Route::post('reservations/{reservation}/delete', 'ReservationController@delete');

    /** Guest Routes */

    /**
     * List your group's guests.
     * WRITTEN.
     */
    Route::get('guests', 'GuestController@index');

    /**
     * Show a specific guest's details.
     * WRITTEN.
     */
    Route::get('guests/{guest}', 'GuestController@show');

    /**
     * Create a guest.
     * WRITTEN.
     */
    Route::post('guests/create', 'GuestController@create');

    /**
     * Update a guest.
     * WRITTEN.
     */
    Route::post('guests/{guest}/update', 'GuestController@update');

    /**
     * Delete a guest.
     * WRITTEN.
     */
    Route::post('guests/{guest}/delete', 'GuestController@delete');
});


/**
 * Submitted invitation form for new guests.
 * WRITTEN.
 */
Route::post('welcome/{invitation_token}', 'InvitationController@submit');
