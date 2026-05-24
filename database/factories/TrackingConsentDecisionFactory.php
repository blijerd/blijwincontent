<?php

namespace Database\Factories;

use App\Models\TrackingConsentDecision;
use App\Models\TrackingVisitor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TrackingConsentDecision>
 */
class TrackingConsentDecisionFactory extends Factory
{
    protected $model = TrackingConsentDecision::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::uuid(),
            'tracking_visitor_id' => TrackingVisitor::factory(),
            'client_identifier' => 'v_'.Str::random(32),
            'source' => 'initial_preferences',
            'necessary_granted' => true,
            'analytics_granted' => true,
            'marketing_granted' => false,
            'storage_mode' => 'cookie',
            'decided_at' => $this->faker->dateTimeBetween('-7 days'),
        ];
    }
}
