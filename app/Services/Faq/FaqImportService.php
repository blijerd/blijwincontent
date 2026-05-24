<?php

namespace App\Services\Faq;

use Illuminate\Support\Str;

class FaqImportService
{
    /** @return array<int, array{question: string, answer_markdown: string}> */
    public function fromHtml(string $html): array
    {
        preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches);

        foreach ($matches[1] ?? [] as $json) {
            $decoded = json_decode(html_entity_decode(trim($json)), true);
            $items = $this->extractFaqSchema($decoded);

            if ($items !== []) {
                return $items;
            }
        }

        return [];
    }

    /** @return array<int, array{question: string, answer_markdown: string}> */
    public function fromMarkdown(string $markdown): array
    {
        preg_match_all('/^\s*#{2,4}\s+(.+?)\s*$([\s\S]*?)(?=^\s*#{2,4}\s+|\z)/m', trim($markdown), $matches, PREG_SET_ORDER);

        return collect($matches)
            ->map(fn (array $match): array => [
                'question' => trim($match[1]),
                'answer_markdown' => trim($match[2]),
            ])
            ->filter(fn (array $item): bool => Str::endsWith($item['question'], '?') && $item['answer_markdown'] !== '')
            ->values()
            ->all();
    }

    private function extractFaqSchema(mixed $decoded): array
    {
        if (! is_array($decoded)) {
            return [];
        }

        if (($decoded['@type'] ?? null) === 'FAQPage') {
            return $this->schemaItems($decoded['mainEntity'] ?? []);
        }

        foreach ($decoded['@graph'] ?? [] as $node) {
            if (is_array($node) && ($node['@type'] ?? null) === 'FAQPage') {
                return $this->schemaItems($node['mainEntity'] ?? []);
            }
        }

        return [];
    }

    private function schemaItems(mixed $entities): array
    {
        if (! is_array($entities)) {
            return [];
        }

        return collect($entities)
            ->map(function (array $entity): array {
                $answer = $entity['acceptedAnswer']['text'] ?? '';

                return [
                    'question' => trim((string) ($entity['name'] ?? '')),
                    'answer_markdown' => trim(strip_tags((string) $answer)),
                ];
            })
            ->filter(fn (array $item): bool => $item['question'] !== '' && $item['answer_markdown'] !== '')
            ->values()
            ->all();
    }
}
