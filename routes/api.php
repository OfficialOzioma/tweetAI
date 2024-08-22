<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AutobotController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/autobot-count', function () {
    return response()->json(['count' => User::count()]);
});

Route::middleware(['throttle:5,1'])->group(function () {
    Route::get('/autobots', [AutobotController::class, 'index']);
    Route::get('/autobots/{autobot}/posts', [AutobotController::class, 'posts']);
    Route::get('/posts/{post}/comments', [AutobotController::class, 'comments']);
});
