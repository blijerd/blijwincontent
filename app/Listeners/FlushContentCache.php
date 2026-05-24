<?php

namespace App\Listeners;

use App\Events\ContentChanged;
use Illuminate\Contracts\Cache\Repository;

class FlushContentCache
{
    public function __construct(private readonly Repository $cache)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(ContentChanged $event): void
    {
        if ($event->page === null) {
            return;
        }

        $this->cache->delete('sitemap:site:'.$event->page->site_id);
    }
}
