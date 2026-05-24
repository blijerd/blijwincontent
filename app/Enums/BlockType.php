<?php

namespace App\Enums;

enum BlockType: string
{
    case Text = 'text';
    case Image = 'image';
    case Button = 'button';
    case Quote = 'quote';
    case FaqItem = 'faq_item';
    case Download = 'download';
    case Video = 'video';
}
