<?php

namespace Database\Factories;

use App\Models\TrackingSession;
use App\Models\TrackingVisitor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TrackingSession>
 */
class TrackingSessionFactory extends Factory
{
    protected $model = TrackingSession::class;

    public function definition(): array
    {
        $now = $this->faker->dateTimeBetween('-7 days');

        return [
            'public_id' => (string) Str::uuid(),
            'tracking_visitor_id' => TrackingVisitor::factory(),
            'identifier' => 's_'.Str::random(32),
            'storage_mode' => 'server_session',
            'first_seen_at' => $now,
            'last_seen_at' => $now,
        ];
    }
}
