<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Invitation;
use App\Models\Group;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Invitation::class, function (Faker $faker) {
    return [
        'group_id' => factory(Group::class),
        'creator_id' => factory(User::class),
        'name' => $faker->words(5, true),
        'token' => Str::random(20)
    ];
});
