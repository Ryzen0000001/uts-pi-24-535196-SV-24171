<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'motor' => fake()->randomElement(['Vario 125', 'Beat Street', 'NMAX 155', 'Supra X 125']),
            'serviced_at' => fake()->dateTimeBetween('-1 year')->format('Y-m-d'),
            'odometer_km' => fake()->numberBetween(1000, 40000),
            'service_type' => fake()->randomElement(['Ganti oli mesin', 'Servis rutin', 'Ganti kampas rem']),
            'cost' => fake()->numberBetween(4, 60) * 5000,
            'workshop' => fake()->randomElement(['AHASS Sleman', 'Bengkel Pak Min']),
            'notes' => null,
        ];
    }
}
