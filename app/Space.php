<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
	protected $primaryKey = 'id';

	public $timestamps = true;

	protected $fillable = [
		'group_id', 'name', 'capacity', 'accommodations', 'notes'
	];

	public function reservations()
	{
		return $this->hasMany('App\Reservation');
	}

	public function group()
	{
		return $this->hasOne('App\Group', 'id', 'group_id');
	}
}
