<?php

namespace Tests\Unit\Faq;

use App\Services\Faq\FaqImportService;
use Tests\TestCase;

class FaqImportServiceTest extends TestCase
{
    public function test_it_imports_faqpage_json_ld_from_html(): void
    {
        $items = app(FaqImportService::class)->fromHtml(<<<'HTML'
            <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "FAQPage",
                "mainEntity": [
                    {
                        "@type": "Question",
                        "name": "Wat kost een disco?",
                        "acceptedAnswer": {"@type": "Answer", "text": "Dat hangt af van het pakket."}
                    }
                ]
            }
            </script>
        HTML);

        $this->assertSame([
            [
                'question' => 'Wat kost een disco?',
                'answer_markdown' => 'Dat hangt af van het pakket.',
            ],
        ], $items);
    }

    public function test_it_imports_markdown_question_headings(): void
    {
        $items = app(FaqImportService::class)->fromMarkdown(<<<'MD'
            ## Wat nemen jullie mee?
            Wij nemen licht, geluid en muziek mee.

            ### Geen vraag
            Deze heading eindigt niet met een vraagteken.

            ### Kunnen kinderen verzoekjes doen?
            Ja, dat kan tijdens het feest.
        MD);

        $this->assertCount(2, $items);
        $this->assertSame('Wat nemen jullie mee?', $items[0]['question']);
        $this->assertSame('Kunnen kinderen verzoekjes doen?', $items[1]['question']);
    }
}
