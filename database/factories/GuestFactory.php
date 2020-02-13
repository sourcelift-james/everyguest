<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Guest;
use App\Models\Group;
use App\Models\Invitation;
use Faker\Generator as Faker;

$factory->define(Guest::class, function (Faker $faker) {
    return [
        'group_id' => factory(Group::class),
        'first' => $faker->firstName,
        'last' => $faker->lastName,
        'phone' => $faker->phoneNumber,
        'email' => $faker->safeEmail,
        'address' => $faker->streetAddress,
        'city' => $faker->city,
        'state' => $faker->state,
        'zip' => $faker->postcode,
        'arrivalMethod' => $faker->randomElement([
            'Flight', 'Rented or Owned Vehicle'
        ]),
        'arrivalTime' => $faker->dateTimeThisMonth(),
        'departureMethod' => $faker->randomElement([
           'Flight', 'Rented or Owned Vehicle'
        ]),
        'departureTime' => $faker->dateTimeThisMonth(),
        'notes' => $faker->sentences(3, true)
    ];
});
