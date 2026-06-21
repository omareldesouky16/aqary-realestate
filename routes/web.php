<?php

use Illuminate\Support\Facades\Route;

use Livewire\Volt\Volt;

Volt::route('/', 'home')->name('home');
Volt::route('/properties', 'property-listing')->name('properties');
Volt::route('/properties/{propertyId}', 'property-detail')->name('property.detail');
Volt::route('/profile/edit', 'user-profile-edit')->name('profile.edit')->middleware('auth');

Route::get('/properties/{id}/pdf', function ($id) {
    return response('PDF Generation for Property ' . $id . ' - Coming Soon!', 200)
        ->header('Content-Type', 'text/plain');
})->name('properties.pdf');
