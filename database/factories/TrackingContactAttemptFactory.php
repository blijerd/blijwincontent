<?php

namespace Database\Factories;

use App\Models\TrackingContactAttempt;
use App\Models\TrackingSession;
use App\Models\TrackingVisitor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TrackingContactAttempt>
 */
class TrackingContactAttemptFactory extends Factory
{
    protected $model = TrackingContactAttempt::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::uuid(),
            'tracking_visitor_id' => TrackingVisitor::factory(),
            'tracking_session_id' => TrackingSession::factory(),
            'event_type' => 'contact_attempt',
            'contact_type' => 'email',
            'href' => 'mailto:info@example.test',
            'link_text' => 'Mail ons',
            'occurred_at' => $this->faker->dateTimeBetween('-7 days'),
        ];
    }
}
