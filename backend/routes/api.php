<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/{id}', [MovieController::class, 'show']);
Route::post('/upload', [MovieController::class, 'upload']);
Route::get('/proxy-image', [MovieController::class, 'proxyImage']);

Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

/*
|--------------------------------------------------------------------------
| 后台管理接口（需要 Sanctum token）
|--------------------------------------------------------------------------
*/
Route::post('/admin/login', [AdminController::class, 'login']);

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/movies', [AdminController::class, 'movies']);
    Route::patch('/movies/{id}/publish', [AdminController::class, 'setPublished']);
});