<?php

namespace App\Models;

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
		return $this->hasOne('App\Models\User', 'id', 'owner_id');
	}

	public function members()
	{
		return $this->hasMany('App\Models\User', 'group_id', 'id');
	}

	public function guests()
	{
		return $this->hasMany('App\Models\Guest');
	}

	public function spaces()
	{
		return $this->hasMany('App\Models\Space');
	}

	public function invitations()
	{
		return $this->hasMany('App\Models\Invitation');
	}

	public function reservations()
	{
		return $this->hasMany('App\Models\Reservation');
	}
}
