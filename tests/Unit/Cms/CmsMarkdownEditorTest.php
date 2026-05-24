<?php

namespace Tests\Unit\Cms;

use App\Support\Filament\CmsMarkdownEditor;
use Tests\TestCase;

class CmsMarkdownEditorTest extends TestCase
{
    public function test_it_configures_safe_markdown_editing_for_cms_content(): void
    {
        $editor = CmsMarkdownEditor::make('body_markdown');

        $this->assertTrue($editor->hasFileAttachments());
        $this->assertSame('public', $editor->getFileAttachmentsDiskName());
        $this->assertSame('cms/markdown', $editor->getFileAttachmentsDirectory());
        $this->assertSame('public', $editor->getFileAttachmentsVisibility());
        $this->assertSame(8192, $editor->getFileAttachmentsMaxSize());
        $this->assertSame(['image/png', 'image/jpeg', 'image/webp', 'image/gif'], $editor->getFileAttachmentsAcceptedFileTypes());
        $this->assertSame('18rem', $editor->getMinHeight());
        $this->assertSame('42rem', $editor->getMaxHeight());
        $this->assertSame([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ], $editor->getCommonMarkOptions());
        $this->assertContains(['table', 'attachFiles'], $editor->getToolbarButtons());
    }
}
