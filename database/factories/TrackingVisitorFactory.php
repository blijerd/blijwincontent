<?php

namespace Database\Factories;

use App\Models\TrackingVisitor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TrackingVisitor>
 */
class TrackingVisitorFactory extends Factory
{
    protected $model = TrackingVisitor::class;

    public function definition(): array
    {
        $now = $this->faker->dateTimeBetween('-7 days');

        return [
            'public_id' => (string) Str::uuid(),
            'identifier' => 'v_'.Str::random(32),
            'first_seen_at' => $now,
            'last_seen_at' => $now,
            'first_referrer' => $this->faker->optional()->url(),
            'first_device' => $this->faker->randomElement(['desktop', 'tablet', 'mobile']),
            'pageview_count' => 0,
            'contact_attempt_count' => 0,
        ];
    }
}
