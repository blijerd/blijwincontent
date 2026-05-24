<?php

namespace Database\Factories;

use App\Models\TrackingPageVisit;
use App\Models\TrackingSession;
use App\Models\TrackingVisitor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TrackingPageVisit>
 */
class TrackingPageVisitFactory extends Factory
{
    protected $model = TrackingPageVisit::class;

    public function definition(): array
    {
        $startedAt = $this->faker->dateTimeBetween('-7 days');

        return [
            'public_id' => (string) Str::uuid(),
            'tracking_visitor_id' => TrackingVisitor::factory(),
            'tracking_session_id' => TrackingSession::factory(),
            'identifier' => 'pv_'.Str::random(32),
            'slug' => 'home',
            'path' => '/',
            'url' => 'https://example.test/',
            'title' => 'Home',
            'device' => 'desktop',
            'landing' => true,
            'started_at' => $startedAt,
            'last_seen_at' => $startedAt,
        ];
    }
}
