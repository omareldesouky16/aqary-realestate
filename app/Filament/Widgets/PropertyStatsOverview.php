<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PropertyStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Properties', Property::count()),
            Stat::make('Total Views', Property::sum('views_count') ?? 0)
                ->description('All time property views')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),
            Stat::make('Total Favorites', Property::sum('favorites_count') ?? 0)
                ->description('All time favorites')
                ->descriptionIcon('heroicon-m-heart')
                ->color('danger'),
        ];
    }
}
