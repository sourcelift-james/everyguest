<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Concerns\UsesUuid;

class Invitation extends Model
{
	use UsesUuid;

	protected $primaryKey = 'id';

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
