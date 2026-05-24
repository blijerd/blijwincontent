<?php

namespace App\Events;

use App\Models\Page;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly ?Page $page)
    {
    }
}
