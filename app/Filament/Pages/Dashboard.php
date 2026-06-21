<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * Determine if the user can access the dashboard.
     * Only admins can see the dashboard, sellers only see their properties.
     */
    public static function canAccess(): bool
    {
        return auth()->user()->role === 'admin';
    }
}
