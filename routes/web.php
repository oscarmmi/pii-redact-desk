<?php

use App\Http\Controllers\DeskController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DeskController::class, 'index'])->name('home');
Route::post('/redact', [DeskController::class, 'redact'])->name('redact');
