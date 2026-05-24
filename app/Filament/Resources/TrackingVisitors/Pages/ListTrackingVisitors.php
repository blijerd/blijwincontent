<?php

namespace App\Filament\Resources\TrackingVisitors\Pages;

use App\Filament\Resources\TrackingVisitors\TrackingVisitorResource;
use Filament\Resources\Pages\ListRecords;

class ListTrackingVisitors extends ListRecords
{
    protected static string $resource = TrackingVisitorResource::class;

    protected static ?string $title = 'Tracking visitors';

    public function getSubheading(): ?string
    {
        return 'Bezoekers, sessies, cookiekeuzes, paginaweergaven en contactpogingen vanuit de publieke website.';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
