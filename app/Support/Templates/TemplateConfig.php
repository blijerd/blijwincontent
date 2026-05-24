<?php

namespace App\Support\Templates;

use App\Enums\SectionType;
use App\Enums\TemplateType;

class TemplateConfig
{
    /** @return list<SectionType> */
    public static function allowedSections(TemplateType $template): array
    {
        return match ($template) {
            TemplateType::LandingPage => [
                SectionType::Hero,
                SectionType::TwoColumns,
                SectionType::Triplets,
                SectionType::Reviews,
                SectionType::SpotlightPanel,
                SectionType::Cta,
                SectionType::Faq,
                SectionType::Video,
            ],
            TemplateType::Product => [
                SectionType::Hero,
                SectionType::RichText,
                SectionType::Triplets,
                SectionType::Reviews,
                SectionType::Faq,
                SectionType::Cta,
            ],
            TemplateType::Blog => [
                SectionType::Hero,
                SectionType::RichText,
                SectionType::Faq,
                SectionType::Cta,
            ],
            TemplateType::Downloads => [
                SectionType::Hero,
                SectionType::RichText,
                SectionType::Triplets,
                SectionType::Cta,
            ],
            TemplateType::Default => [
                SectionType::Hero,
                SectionType::RichText,
                SectionType::TwoColumns,
                SectionType::Cta,
            ],
        };
    }

    public static function view(TemplateType $template): string
    {
        return match ($template) {
            TemplateType::LandingPage => 'cms.pages.landingpage',
            TemplateType::Product => 'cms.pages.product',
            TemplateType::Blog => 'cms.pages.blog',
            TemplateType::Downloads => 'cms.pages.downloads',
            TemplateType::Default => 'cms.pages.default',
        };
    }
}
