<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        $cities = ['Phnom Penh', 'Siem Reap', 'Battambang', 'Sihanoukville', 'Kampot', 'Poipet', 'Kampong Cham'];
        $city = fake()->randomElement($cities);

        return [
            'user_id' => User::factory()->customer(),
            'label' => fake()->randomElement(['Home', 'Work', 'Office', 'Villa']),
            'address' => fake()->streetAddress() . ', Sangkat ' . fake()->word(),
            'city' => $city,
            'phone' => '+855 ' . fake()->numerify('## ### ###'),
            'is_default' => false,
        ];
    }
}
