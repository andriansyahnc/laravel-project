<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class KostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence(),
            'room_area' => $this->faker->randomNumber(5, false),
            'location' => $this->faker->name(),
            'price' => $this->faker->randomNumber(7, false),
            'user_id' => $this->faker->randomNumber(5, false),
        ];
    }
}
