<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
	public $timestamps = true;

	protected $dates = [
		'expired_at'
	];

	protected $casts = [
	    'details' => 'array'
    ];

	protected $fillable = [
		'group_id',
        'name',
        'creator_id',
        'expired_at',
        'token',
        'details'
	];

	public function guests()
	{
		return $this->hasMany('App\Models\Guest');
	}

	public function group()
	{
		return $this->hasOne('App\Models\Group', 'id', 'group_id');
	}
}
