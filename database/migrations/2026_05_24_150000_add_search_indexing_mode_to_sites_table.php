<?php

use App\Enums\SearchIndexingMode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table): void {
            $table->string('search_indexing_mode', 24)
                ->default(SearchIndexingMode::Index->value)
                ->after('is_active')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table): void {
            $table->dropColumn('search_indexing_mode');
        });
    }
};
