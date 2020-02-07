<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Reservation;
use App\Models\Group;
use App\Models\Guest;
use App\Models\Space;
use Faker\Generator as Faker;

$factory->define(Reservation::class, function (Faker $faker) {
    return [
        'guest_id' => factory(Guest::class),
        'space_id' => factory(Space::class),
        'group_id' => factory(Group::class),
        'starts_at' => $faker->datetimeThisMonth(),
        'ends_at' => $faker->dateTimeThisMonth(),
        'notes' => $faker->sentences(2, true)
    ];
});
