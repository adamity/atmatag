<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('pages.landing');
});

Route::get('/webhook', [TelegramController::class, 'webhook']); // Checked
Route::get('/tag/{contact_id}', [MessageController::class, 'index'])->name('tag');
Route::post('/send', [MessageController::class, 'send'])->name('send');
