<?php

namespace App\Services\Content;

use Illuminate\Contracts\Cache\Repository;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class MarkdownRenderService
{
    public function __construct(private readonly Repository $cache)
    {
    }

    public function render(?string $markdown, string $context = 'default'): string
    {
        $markdown = trim((string) $markdown);

        if ($markdown === '') {
            return '';
        }

        return $this->cache->rememberForever(
            'markdown:'.sha1($context.'|'.$markdown),
            fn (): string => $this->renderUncached($markdown),
        );
    }

    private function renderUncached(string $markdown): string
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'heading_permalink' => [
                'html_class' => 'heading-anchor',
                'id_prefix' => 'content',
                'fragment_prefix' => 'content',
            ],
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new HeadingPermalinkExtension());

        $html = (new MarkdownConverter($environment))->convert($markdown)->getContent();

        return (new HtmlSanitizer($this->sanitizerConfig()))->sanitize($html);
    }

    private function sanitizerConfig(): HtmlSanitizerConfig
    {
        return (new HtmlSanitizerConfig())
            ->allowSafeElements()
            ->allowElement('h1', ['id', 'class'])
            ->allowElement('h2', ['id', 'class'])
            ->allowElement('h3', ['id', 'class'])
            ->allowElement('h4', ['id', 'class'])
            ->allowElement('a', ['href', 'title', 'rel', 'target', 'class'])
            ->allowElement('img', ['src', 'alt', 'title', 'width', 'height', 'loading']);
    }
}
