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
	Route::get('about', 'SPAController@about');
	Route::get('getuser', 'SPAController@getUser');

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

});
