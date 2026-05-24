<?php

namespace Tests\Feature\Downloads;

use App\Models\DownloadCategory;
use App\Models\DownloadFormat;
use App\Models\DownloadItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_serves_public_disk_files_through_direct_route(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('downloads/test.pdf', 'pdf-content');

        $category = DownloadCategory::factory()->create();
        $item = DownloadItem::factory()->create(['download_category_id' => $category->id, 'title' => 'Test brochure']);
        $format = DownloadFormat::factory()->create([
            'download_item_id' => $item->id,
            'label' => 'PDF',
            'file_path' => 'downloads/test.pdf',
        ]);

        $this->get(route('downloads.file', [
            'category' => $category->public_id,
            'item' => $item->public_id,
            'format' => $format->public_id,
        ]))->assertOk();
    }
}
