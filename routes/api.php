<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// AI Integrated Endpoints
Route::post('/recommendations', [ApiController::class, 'getRecommendations']);
Route::post('/chat', [ApiController::class, 'chat'])->middleware('auth:sanctum');
