<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
	protected $primaryKey = 'id';

	public $timestamps = true;

	protected $fillable = [
		'name', 'owner_id'
	];

	public function owner()
	{
		return $this->hasOne('App\User', 'id', 'owner_id');
	}

	public function members()
	{
		return $this->hasMany('App\User', 'group_id', 'id');
	}

	public function guests()
	{
		return $this->hasMany('App\Guest');
	}

	public function spaces()
	{
		return $this->hasMany('App\Space');
	}

	public function invitations()
	{
		return $this->hasMany('App\Invitation');
	}

	public function reservations()
	{
		return $this->hasMany('App\Reservation');
	}
}
