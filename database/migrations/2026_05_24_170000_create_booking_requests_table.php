<?php

use App\Enums\BookingRequestAvailabilityStatus;
use App\Enums\BookingRequestEmailConfirmationStatus;
use App\Enums\BookingRequestSyncStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_requests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('sync_status')->default(BookingRequestSyncStatus::Pending->value)->index();
            $table->string('availability_status')->default(BookingRequestAvailabilityStatus::Unknown->value)->index();
            $table->string('email_confirmation_status')->default(BookingRequestEmailConfirmationStatus::Pending->value)->index();
            $table->string('blijwinos_public_id')->nullable()->index();
            $table->string('event_type')->nullable()->index();
            $table->string('package_slug')->nullable()->index();
            $table->date('requested_date')->nullable()->index();
            $table->time('requested_start_time')->nullable();
            $table->date('alternative_date')->nullable();
            $table->time('alternative_start_time')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedSmallInteger('guest_count')->nullable();
            $table->string('location_name')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code', 32)->nullable();
            $table->string('city')->nullable();
            $table->string('contact_first_name');
            $table->string('contact_last_name')->nullable();
            $table->string('organization')->nullable();
            $table->string('email')->index();
            $table->string('phone', 64)->nullable();
            $table->text('notes_markdown')->nullable();
            $table->boolean('privacy_accepted')->default(false);
            $table->json('payload');
            $table->json('blijwinos_response')->nullable();
            $table->unsignedSmallInteger('sync_attempts')->default(0);
            $table->timestamp('last_attempted_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable()->index();
            $table->text('last_error')->nullable();
            $table->string('source_url')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
    }
};
