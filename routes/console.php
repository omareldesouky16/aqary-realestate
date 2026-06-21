<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('make:admin {email} {password}', function ($email, $password) {
    User::updateOrCreate(
        ['email' => $email],
        [
            'name' => 'Aqary Admin',
            'password' => Hash::make($password),
            'role' => 'admin',
            'is_verified' => true
        ]
    );
    $this->info("Admin user {$email} created/updated successfully!");
})->purpose('Create or update an admin user');
