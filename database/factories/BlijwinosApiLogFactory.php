<?php

namespace Database\Factories;

use App\Models\BlijwinosApiLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BlijwinosApiLog> */
class BlijwinosApiLogFactory extends Factory
{
    protected $model = BlijwinosApiLog::class;

    public function definition(): array
    {
        return [
            'direction' => fake()->randomElement(['read', 'write']),
            'method' => fake()->randomElement(['GET', 'POST']),
            'endpoint' => '/api/blijwinboekingen/catalogus',
            'status_code' => 200,
            'successful' => true,
            'duration_ms' => fake()->numberBetween(20, 800),
            'request_id' => fake()->uuid(),
            'metadata' => [],
        ];
    }
}
