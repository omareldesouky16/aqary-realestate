<?php

use Illuminate\Support\Facades\Route;

use Livewire\Volt\Volt;

Volt::route('/', 'home')->name('home');
Volt::route('/properties', 'property-listing')->name('properties');
Volt::route('/properties/{propertyId}', 'property-detail')->name('property.detail');
Volt::route('/profile/edit', 'user-profile-edit')->name('profile.edit')->middleware('auth');

Volt::route('/login', 'auth.login')->name('login')->middleware('guest');
Volt::route('/register', 'auth.register-buyer')->name('register')->middleware('guest');
Volt::route('/seller/register', 'auth.register-seller')->name('seller.register')->middleware('guest');

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::get('/properties/{id}/pdf', function ($id) {
    return response('PDF Generation for Property ' . $id . ' - Coming Soon!', 200)
        ->header('Content-Type', 'text/plain');
})->name('properties.pdf');
