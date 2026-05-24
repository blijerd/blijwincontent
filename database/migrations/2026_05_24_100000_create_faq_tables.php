<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('public_id')->unique();
            $table->string('locale', 12)->nullable()->index();
            $table->string('title');
            $table->string('slug')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['site_id', 'locale', 'slug']);
        });

        Schema::create('faq_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('faq_category_id')->constrained()->cascadeOnDelete();
            $table->uuid('public_id')->unique();
            $table->string('question');
            $table->text('answer_markdown');
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_published')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('faq_category_section', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('faq_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['faq_category_id', 'section_id']);
        });

        Schema::table('sections', function (Blueprint $table): void {
            $table->string('faq_keyword')->nullable()->after('intro_markdown');
            $table->boolean('faq_searchable')->default(true)->after('faq_keyword');
            $table->boolean('faq_categories_enabled')->default(true)->after('faq_searchable');
            $table->boolean('faq_schema_enabled')->default(true)->after('faq_categories_enabled');
            $table->boolean('faq_expand_first')->default(false)->after('faq_schema_enabled');
            $table->boolean('faq_allow_multiple_open')->default(false)->after('faq_expand_first');
            $table->unsignedInteger('faq_initial_limit')->default(0)->after('faq_allow_multiple_open');
            $table->string('faq_cta_label')->nullable()->after('faq_initial_limit');
            $table->string('faq_cta_url')->nullable()->after('faq_cta_label');
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table): void {
            $table->dropColumn([
                'faq_keyword',
                'faq_searchable',
                'faq_categories_enabled',
                'faq_schema_enabled',
                'faq_expand_first',
                'faq_allow_multiple_open',
                'faq_initial_limit',
                'faq_cta_label',
                'faq_cta_url',
            ]);
        });

        Schema::dropIfExists('faq_category_section');
        Schema::dropIfExists('faq_items');
        Schema::dropIfExists('faq_categories');
    }
};
