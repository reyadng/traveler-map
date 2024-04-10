<?php

use App\Http\Controllers\TestController;
use App\Http\Controllers\WhController;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::post('/wh', [WhController::class, 'index'])
    ->withoutMiddleware(VerifyCsrfToken::class);
Route::get('/test', [TestController::class, 'index']);
