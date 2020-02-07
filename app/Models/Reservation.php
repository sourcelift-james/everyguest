<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
	public $timestamps = true;

	protected $dates = [
		'starts_at', 'ends_at'
	];

	protected $fillable = [
		'guest_id', 'space_id', 'group_id', 'starts_at', 'ends_at', 'notes'
	];

	public function guest()
	{
		return $this->hasOne('App\Models\Guest');
	}

	public function space()
	{
		return $this->hasOne('App\Models\Space');
	}

	public function group()
	{
		return $this->hasOne('App\Models\Group', 'id', 'group_id');
	}
}
