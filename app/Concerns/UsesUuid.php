<?php

namespace App\Concerns;

use Illuminate\Support\Str;

trait UsesUuid
{
	public $incrementing = false;

	protected $keyType = 'string';

    protected static function bootUsesUuid()
    {
        static::creating(function ($model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}
