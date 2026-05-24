<?php

namespace Tests\Feature\Downloads;

use App\Models\DownloadCategory;
use App\Models\DownloadFormat;
use App\Models\DownloadItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DownloadSecureRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_token_and_logs_secure_mail_request(): void
    {
        Mail::fake();

        $category = DownloadCategory::factory()->create();
        $item = DownloadItem::factory()->create(['download_category_id' => $category->id]);
        $format = DownloadFormat::factory()->secure()->create(['download_item_id' => $item->id]);
        $formToken = 'test-token';

        $this->withSession([
            config('settings.downloads.secure.form_token_session_key').'.'.$formToken => now()->subSeconds(10)->timestamp,
        ])->postJson('/downloads/api/request-email', [
            'first_name' => 'Edwin',
            'email' => 'edwin@example.com',
            'category_id' => $category->public_id,
            'item_id' => $item->public_id,
            'format_id' => $format->public_id,
            'form_token' => $formToken,
            config('settings.downloads.secure.honeypot_field') => '',
        ])->assertOk()
            ->assertJsonPath('message', 'We hebben de downloadlink naar je e-mailadres gestuurd.');

        $this->assertDatabaseCount('download_secure_tokens', 1);
        $this->assertDatabaseHas('download_mail_logs', [
            'download_format_id' => $format->id,
            'email' => 'edwin@example.com',
            'status' => 'sent',
        ]);
    }
}
