<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('public_id')->unique();
            $table->string('locale', 12)->nullable()->index();
            $table->string('title');
            $table->string('slug')->index();
            $table->text('intro_markdown')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['site_id', 'locale', 'slug']);
        });

        Schema::create('download_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('download_category_id')->constrained()->cascadeOnDelete();
            $table->uuid('public_id')->unique();
            $table->string('title');
            $table->text('preview_markdown')->nullable();
            $table->foreignId('preview_image_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('preview_image_alt')->nullable();
            $table->string('preview_image_focus')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('download_formats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('download_item_id')->constrained()->cascadeOnDelete();
            $table->uuid('public_id')->unique();
            $table->string('label');
            $table->string('file_path');
            $table->boolean('is_secure')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('download_category_section', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('download_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['download_category_id', 'section_id']);
        });

        Schema::create('download_secure_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('download_format_id')->constrained()->cascadeOnDelete();
            $table->string('token', 96)->unique();
            $table->string('first_name');
            $table->string('email')->index();
            $table->timestamp('expires_at')->index();
            $table->unsignedInteger('access_count')->default(0);
            $table->timestamp('last_accessed_at')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('download_mail_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('download_format_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('download_secure_token_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('status')->index();
            $table->text('message')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->timestamps();
        });

        Schema::table('sections', function (Blueprint $table): void {
            $table->boolean('downloads_show_category_intro')->default(true)->after('faq_cta_url');
            $table->boolean('downloads_secure_enabled')->default(true)->after('downloads_show_category_intro');
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table): void {
            $table->dropColumn([
                'downloads_show_category_intro',
                'downloads_secure_enabled',
            ]);
        });

        Schema::dropIfExists('download_mail_logs');
        Schema::dropIfExists('download_secure_tokens');
        Schema::dropIfExists('download_category_section');
        Schema::dropIfExists('download_formats');
        Schema::dropIfExists('download_items');
        Schema::dropIfExists('download_categories');
    }
};
