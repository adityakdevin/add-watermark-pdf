<?php

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

Route::get('/', function () {
    return view('welcome');
});
// routes/web.php
Route::get('/add-watermark', [\App\Http\Controllers\PdfController::class,'uploadForm']);
Route::post('/generate-pdf', [\App\Http\Controllers\PdfController::class,'addWatermark'])->name('generate-pdf') ;
