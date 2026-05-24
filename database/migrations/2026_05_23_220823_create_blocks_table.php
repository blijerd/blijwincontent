<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->uuid('public_id')->unique();
            $table->string('type', 50)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('heading')->nullable();
            $table->string('subheading')->nullable();
            $table->text('body_markdown')->nullable();
            $table->string('button_label')->nullable();
            $table->string('button_url')->nullable();
            $table->foreignId('image_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
