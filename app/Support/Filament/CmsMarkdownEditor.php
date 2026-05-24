<?php

namespace App\Support\Filament;

use Filament\Forms\Components\MarkdownEditor;

class CmsMarkdownEditor
{
    public static function make(
        string $name,
        ?string $helperText = null,
        string $minHeight = '18rem',
        ?int $maxLength = null,
    ): MarkdownEditor {
        $editor = MarkdownEditor::make($name)
            ->toolbarButtons([
                ['bold', 'italic', 'strike', 'link'],
                ['heading'],
                ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                ['table', 'attachFiles'],
                ['undo', 'redo'],
            ])
            ->fileAttachments(true)
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsDirectory('cms/markdown')
            ->fileAttachmentsAcceptedFileTypes([
                'image/png',
                'image/jpeg',
                'image/webp',
                'image/gif',
            ])
            ->fileAttachmentsMaxSize(8192)
            ->commonMarkOptions([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ])
            ->helperText($helperText ?? 'Schrijf in Markdown. HTML wordt gestript en afbeeldingen worden opgeslagen in de CMS media-map.')
            ->hint('Markdown + preview')
            ->minHeight($minHeight)
            ->maxHeight('42rem')
            ->columnSpanFull();

        if ($maxLength !== null) {
            $editor->maxLength($maxLength);
        }

        return $editor;
    }
}
