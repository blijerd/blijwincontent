<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('navigation_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('handle', 64);
            $table->string('title');
            $table->string('locale', 12)->default('nl')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->string('source_system')->nullable()->index();
            $table->string('source_path')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'locale', 'handle']);
        });

        Schema::create('navigation_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('navigation_menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('navigation_menu_items')->nullOnDelete();
            $table->foreignId('page_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label');
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_visible')->default(true)->index();
            $table->boolean('opens_in_new_tab')->default(false);
            $table->string('source_system')->nullable()->index();
            $table->string('source_path')->nullable();
            $table->string('source_key')->nullable();
            $table->timestamps();

            $table->index(['navigation_menu_id', 'parent_id', 'sort_order'], 'navigation_items_tree_index');
            $table->unique(['navigation_menu_id', 'source_system', 'source_path'], 'navigation_items_source_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_menu_items');
        Schema::dropIfExists('navigation_menus');
    }
};
