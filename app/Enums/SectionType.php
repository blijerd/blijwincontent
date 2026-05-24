<?php

namespace App\Enums;

enum SectionType: string
{
    case Hero = 'hero';
    case TwoColumns = 'two_columns';
    case Triplets = 'triplets';
    case Reviews = 'reviews';
    case SpotlightPanel = 'spotlight_panel';
    case RichText = 'rich_text';
    case Cta = 'cta';
    case Faq = 'faq';
    case Video = 'video';
}
