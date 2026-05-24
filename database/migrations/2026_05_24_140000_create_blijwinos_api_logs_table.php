<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blijwinos_api_logs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('direction')->index();
            $table->string('method', 12);
            $table->string('endpoint');
            $table->unsignedSmallInteger('status_code')->nullable()->index();
            $table->boolean('successful')->default(false)->index();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('request_id', 128)->nullable()->index();
            $table->string('error_class')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blijwinos_api_logs');
    }
};
