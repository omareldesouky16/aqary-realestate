<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use Filament\Widgets\ChartWidget;

class PropertyEngagementChart extends ChartWidget
{
    protected static ?string $heading = 'Property Views Engagement';

    protected function getData(): array
    {
        $properties = Property::orderByDesc('views_count')->take(5)->get();

        return [
            'datasets' => [
                [
                    'label' => 'Views',
                    'data' => $properties->pluck('views_count')->toArray(),
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $properties->pluck('title')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
