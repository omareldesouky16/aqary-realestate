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

Route::get('/seed-test-data', function () {
    $user = \App\Models\User::firstOrCreate(
        ['email' => 'test@example.com'],
        ['name' => 'Test User', 'password' => \Illuminate\Support\Facades\Hash::make('password')]
    );

    \Illuminate\Support\Facades\DB::table('properties')->truncate();

    $properties = [
        ['title' => 'Modern Apartment in New Cairo', 'property_type' => 'apartment', 'city' => 'Cairo', 'region' => 'New Cairo', 'price' => 2500000, 'area_sqm' => 140, 'payment_type' => 'cash', 'bedrooms' => 2, 'bathrooms' => 2, 'is_furnished' => true, 'features' => json_encode(['Security', 'Parking']), 'seller_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
        ['title' => 'Luxury Apartment in New Cairo', 'property_type' => 'apartment', 'city' => 'Cairo', 'region' => 'New Cairo', 'price' => 3500000, 'area_sqm' => 180, 'payment_type' => 'cash', 'bedrooms' => 3, 'bathrooms' => 2, 'is_furnished' => false, 'features' => json_encode(['Security', 'Parking', 'Garden']), 'seller_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
        ['title' => 'Spacious Villa in Sheikh Zayed', 'property_type' => 'villa', 'city' => 'Giza', 'region' => 'Sheikh Zayed', 'price' => 5000000, 'area_sqm' => 350, 'payment_type' => 'cash', 'bedrooms' => 5, 'bathrooms' => 4, 'is_furnished' => false, 'features' => json_encode(['Security', 'Garden']), 'seller_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
        ['title' => 'Cozy Apartment in Maadi', 'property_type' => 'apartment', 'city' => 'Cairo', 'region' => 'Maadi', 'price' => 1800000, 'area_sqm' => 100, 'payment_type' => 'cash', 'bedrooms' => 2, 'bathrooms' => 1, 'is_furnished' => true, 'features' => json_encode(['Parking']), 'seller_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
        ['title' => 'Family Townhouse in New Cairo', 'property_type' => 'house', 'city' => 'Cairo', 'region' => 'New Cairo', 'price' => 4200000, 'area_sqm' => 250, 'payment_type' => 'cash', 'bedrooms' => 4, 'bathrooms' => 3, 'is_furnished' => false, 'features' => json_encode(['Security', 'Garden']), 'seller_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
        ['title' => 'Elegant Villa in Maadi', 'property_type' => 'villa', 'city' => 'Cairo', 'region' => 'Maadi', 'price' => 6000000, 'area_sqm' => 400, 'payment_type' => 'cash', 'bedrooms' => 6, 'bathrooms' => 5, 'is_furnished' => true, 'features' => json_encode(['Security', 'Parking', 'Garden', 'Pool']), 'seller_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
        ['title' => 'Affordable Studio in New Cairo', 'property_type' => 'studio', 'city' => 'Cairo', 'region' => 'New Cairo', 'price' => 1500000, 'area_sqm' => 60, 'payment_type' => 'cash', 'bedrooms' => 1, 'bathrooms' => 1, 'is_furnished' => true, 'features' => json_encode(['Security']), 'seller_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
    ];

    \Illuminate\Support\Facades\DB::table('properties')->insert($properties);

    return response()->json([
        'message' => 'Properties seeded successfully.',
        'count' => \Illuminate\Support\Facades\DB::table('properties')->count()
    ]);
});
