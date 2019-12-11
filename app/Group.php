<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Concerns\UsesUuid as UsesUuid;

class Group extends Model
{
	use UsesUuid;

	protected $primaryKey = 'id';

	public $timestamps = true;

	protected $fillable = [
		'name', 'owner_id'
	];

	public function owner()
	{
		return $this->hasOne('App\User', 'id', 'owner_id');
	}
}
