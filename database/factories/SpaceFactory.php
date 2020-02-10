<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Space;
use Faker\Generator as Faker;
use App\Models\Group;

$factory->define(Space::class, function (Faker $faker) {
    return [
        'group_id' => factory(Group::class),
        'name' => $faker->secondaryAddress,
        'capacity' => $faker->numberBetween(1, 5),
        'accommodations' => $faker->sentences(3, true),
        'notes' => $faker->sentences(2, true),
    ];
});
