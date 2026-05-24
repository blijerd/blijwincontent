<?php

namespace App\Http\Controllers\Bookings;

use App\Actions\Blijwinos\ReadBlijwinosCatalogAction;
use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\View\View;

class BookingRequestFormController extends Controller
{
    public function __invoke(ReadBlijwinosCatalogAction $catalog): View
    {
        return view('bookings.request-form', [
            'site' => Site::query()->where('is_active', true)->first(),
            'catalogOptions' => $this->catalogOptions($catalog),
        ]);
    }

    /** @return array<int, array{value: string, label: string}> */
    private function catalogOptions(ReadBlijwinosCatalogAction $catalog): array
    {
        try {
            $items = collect($catalog->handle()['data'] ?? []);

            $options = $items
                ->map(fn (array $item): array => [
                    'value' => (string) ($item['slug'] ?? ''),
                    'label' => (string) ($item['title'] ?? $item['name'] ?? $item['slug'] ?? ''),
                ])
                ->filter(fn (array $item): bool => $item['value'] !== '' && $item['label'] !== '')
                ->values()
                ->all();

            return $options !== [] ? $options : $this->defaultOptions();
        } catch (\Throwable) {
            return $this->defaultOptions();
        }
    }

    /** @return array<int, array{value: string, label: string}> */
    private function defaultOptions(): array
    {
        return [
            ['value' => 'kinderdisco', 'label' => 'Kinderdisco'],
            ['value' => 'schuimparty', 'label' => 'Schuimparty'],
            ['value' => 'schoolfeest', 'label' => 'Schoolfeest'],
            ['value' => 'groep8-eindfeest', 'label' => 'Groep 8 eindfeest'],
        ];
    }
}
