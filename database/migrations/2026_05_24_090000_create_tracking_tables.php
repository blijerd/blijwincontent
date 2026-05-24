<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_visitors', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('identifier')->unique();
            $table->dateTime('first_seen_at');
            $table->dateTime('last_seen_at');
            $table->string('first_referrer')->nullable();
            $table->string('first_device', 32)->nullable();
            $table->string('first_utm_source')->nullable();
            $table->string('first_utm_medium')->nullable();
            $table->string('first_utm_campaign')->nullable();
            $table->string('first_utm_content')->nullable();
            $table->string('first_utm_term')->nullable();
            $table->string('first_gclid')->nullable();
            $table->string('first_fbclid')->nullable();
            $table->unsignedInteger('pageview_count')->default(0);
            $table->unsignedInteger('contact_attempt_count')->default(0);
            $table->timestamps();
        });

        Schema::create('tracking_sessions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('tracking_visitor_id')->constrained('tracking_visitors')->cascadeOnDelete();
            $table->string('identifier')->unique();
            $table->string('storage_mode', 32)->default('server_session');
            $table->dateTime('first_seen_at');
            $table->dateTime('last_seen_at');
            $table->unsignedInteger('pageview_count')->default(0);
            $table->unsignedInteger('contact_attempt_count')->default(0);
            $table->timestamps();
        });

        Schema::create('tracking_page_visits', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('tracking_visitor_id')->constrained('tracking_visitors')->cascadeOnDelete();
            $table->foreignId('tracking_session_id')->constrained('tracking_sessions')->cascadeOnDelete();
            $table->string('identifier')->unique();
            $table->string('slug')->nullable();
            $table->string('path')->nullable();
            $table->text('url')->nullable();
            $table->string('title')->nullable();
            $table->text('referrer')->nullable();
            $table->string('device', 32)->nullable();
            $table->boolean('landing')->default(false);
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('gclid')->nullable();
            $table->string('fbclid')->nullable();
            $table->unsignedInteger('heartbeat_count')->default(0);
            $table->unsignedInteger('estimated_seconds')->default(0);
            $table->dateTime('started_at');
            $table->dateTime('last_seen_at');
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();

            $table->index(['tracking_visitor_id', 'started_at']);
            $table->index(['tracking_session_id', 'started_at']);
        });

        Schema::create('tracking_contact_attempts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('tracking_visitor_id')->constrained('tracking_visitors')->cascadeOnDelete();
            $table->foreignId('tracking_session_id')->constrained('tracking_sessions')->cascadeOnDelete();
            $table->foreignId('tracking_page_visit_id')->nullable()->constrained('tracking_page_visits')->nullOnDelete();
            $table->string('event_type', 32);
            $table->string('contact_type', 32)->nullable();
            $table->string('slug')->nullable();
            $table->string('path')->nullable();
            $table->text('url')->nullable();
            $table->string('title')->nullable();
            $table->text('referrer')->nullable();
            $table->string('device', 32)->nullable();
            $table->boolean('landing')->default(false);
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('gclid')->nullable();
            $table->string('fbclid')->nullable();
            $table->text('href')->nullable();
            $table->string('link_text', 500)->nullable();
            $table->text('form_action')->nullable();
            $table->string('form_id')->nullable();
            $table->string('form_name')->nullable();
            $table->string('form_method', 16)->nullable();
            $table->dateTime('occurred_at');
            $table->timestamps();

            $table->index(['tracking_visitor_id', 'occurred_at']);
            $table->index(['tracking_session_id', 'occurred_at']);
        });

        Schema::create('tracking_consent_decisions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('tracking_visitor_id')->nullable()->constrained('tracking_visitors')->nullOnDelete();
            $table->string('client_identifier')->nullable();
            $table->string('source', 64)->nullable();
            $table->boolean('necessary_granted')->default(true);
            $table->boolean('analytics_granted')->default(false);
            $table->boolean('marketing_granted')->default(false);
            $table->string('storage_mode', 32)->default('server_session');
            $table->string('request_ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('decided_at');
            $table->timestamps();

            $table->index(['tracking_visitor_id', 'decided_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_consent_decisions');
        Schema::dropIfExists('tracking_contact_attempts');
        Schema::dropIfExists('tracking_page_visits');
        Schema::dropIfExists('tracking_sessions');
        Schema::dropIfExists('tracking_visitors');
    }
};
