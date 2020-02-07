<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
	public $timestamps = true;

	protected $casts = [
		'custom' => 'array',
		'arrDetails' => 'array'
	];

	protected $fillable = [
		'group_id',
        'invitation_id',
        'name',
        'phone',
        'email',
        'arrMethod',
        'arrDetails',
        'notes',
        'custom'
	];

	public function invitation()
	{
		return $this->belongsTo('App\Models\Invitation');
	}

	public function group()
	{
		return $this->belongsTo('App\Models\Group');
	}

	public function reservations()
	{
		return $this->hasMany('App\Models\Reservation');
	}
}
