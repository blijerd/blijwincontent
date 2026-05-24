<?php

namespace App\Enums;

enum TemplateType: string
{
    case Default = 'default';
    case LandingPage = 'landingpage';
    case Product = 'product';
    case Blog = 'blog';
    case Downloads = 'downloads';
}
