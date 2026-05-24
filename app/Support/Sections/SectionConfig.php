<?php

namespace App\Support\Sections;

use App\Enums\SectionType;

class SectionConfig
{
    /** @return array<string, list<string>> */
    public static function validationRules(SectionType $type): array
    {
        return match ($type) {
            SectionType::Hero => ['title' => ['required', 'string', 'max:255']],
            SectionType::Faq => ['title' => ['nullable', 'string', 'max:255']],
            default => ['title' => ['nullable', 'string', 'max:255']],
        };
    }

    public static function partial(SectionType $type): string
    {
        return 'cms.sections.'.$type->value;
    }
}
