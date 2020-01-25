<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
	public $timestamps = true;

	protected $casts = [
		'custom' => 'array',
		'arrDetails' => 'array'
	];

	protected $fillable = [
		'group_id', 'invitation_id', 'name', 'phone', 'email', 'arrMethod', 'arrDetails', 'notes', 'custom'
	];

	public function invitation()
	{
		return $this->hasOne('App\Invitation', 'id', 'invitation_id');
	}

	public function group()
	{
		return $this->hasOne('App\Group', 'id', 'group_id');
	}

	public function reservations()
	{
		return $this->hasMany('App\Reservation');
	}
}
