<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
	public $timestamps = true;

    /**
     * Base keys for form submissions.
     * @var array
     */
    public static $baseKeys = [
        'first', 'last', 'phone', 'email', 'address', 'city', 'state', 'zip', 'arrivalMethod', 'arrivalTime', 'departureMethod', 'departureTime'
    ];

	protected $casts = [
        'details' => 'array',
	];

	protected $fillable = [
		'group_id',
        'first',
        'last',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'zip',
        'arrivalMethod',
        'arrivalTime',
        'departureMethod',
        'departureTime',
        'details',
        'notes'
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

	public function baseKeys()
    {
        return $this->baseKeys;
    }
}
