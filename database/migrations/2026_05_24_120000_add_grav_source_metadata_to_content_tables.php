<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->string('source_system', 50)->nullable()->after('robots_follow')->index();
            $table->string('source_path')->nullable()->after('source_system')->index();
            $table->string('source_folder')->nullable()->after('source_path');
            $table->string('source_template')->nullable()->after('source_folder');
            $table->unsignedInteger('source_order_prefix')->nullable()->after('source_template')->index();
            $table->json('source_frontmatter')->nullable()->after('source_order_prefix');
            $table->boolean('is_routable')->default(true)->after('source_frontmatter')->index();
            $table->boolean('is_visible_in_navigation')->default(true)->after('is_routable')->index();

            $table->unique(['site_id', 'locale', 'source_system', 'source_path'], 'pages_source_unique');
        });

        Schema::table('sections', function (Blueprint $table): void {
            $table->string('source_system', 50)->nullable()->after('is_visible')->index();
            $table->string('source_path')->nullable()->after('source_system')->index();
            $table->string('source_folder')->nullable()->after('source_path');
            $table->string('source_template')->nullable()->after('source_folder');
            $table->unsignedInteger('source_order_prefix')->nullable()->after('source_template')->index();
            $table->json('source_frontmatter')->nullable()->after('source_order_prefix');

            $table->unique(['page_id', 'source_system', 'source_path'], 'sections_source_unique');
        });

        Schema::table('blocks', function (Blueprint $table): void {
            $table->string('source_system', 50)->nullable()->after('image_id')->index();
            $table->string('source_path')->nullable()->after('source_system')->index();
            $table->string('source_key')->nullable()->after('source_path');
            $table->json('source_payload')->nullable()->after('source_key');

            $table->unique(['section_id', 'source_system', 'source_path', 'source_key'], 'blocks_source_unique');
        });

        Schema::table('media_assets', function (Blueprint $table): void {
            $table->string('source_system', 50)->nullable()->after('locale')->index();
            $table->string('source_path')->nullable()->after('source_system')->index();
            $table->string('source_page_path')->nullable()->after('source_path')->index();
            $table->json('source_metadata')->nullable()->after('source_page_path');

            $table->unique(['source_system', 'source_path'], 'media_assets_source_unique');
        });
    }

    public function down(): void
    {
        Schema::table('media_assets', function (Blueprint $table): void {
            $table->dropUnique('media_assets_source_unique');
            $table->dropColumn(['source_system', 'source_path', 'source_page_path', 'source_metadata']);
        });

        Schema::table('blocks', function (Blueprint $table): void {
            $table->dropUnique('blocks_source_unique');
            $table->dropColumn(['source_system', 'source_path', 'source_key', 'source_payload']);
        });

        Schema::table('sections', function (Blueprint $table): void {
            $table->dropUnique('sections_source_unique');
            $table->dropColumn([
                'source_system',
                'source_path',
                'source_folder',
                'source_template',
                'source_order_prefix',
                'source_frontmatter',
            ]);
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->dropUnique('pages_source_unique');
            $table->dropColumn([
                'source_system',
                'source_path',
                'source_folder',
                'source_template',
                'source_order_prefix',
                'source_frontmatter',
                'is_routable',
                'is_visible_in_navigation',
            ]);
        });
    }
};
