<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
	public $timestamps = true;

	protected $dates = [
		'expired_at'
	];

	protected $fillable = [
		'group_id', 'creator_id', 'expired_at'
	];

	public function guests()
	{
		return $this->hasMany('App\Guest');
	}

	public function group()
	{
		return $this->hasOne('App\Group', 'id', 'group_id');
	}
}
