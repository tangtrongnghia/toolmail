<?php

use App\Http\Controllers\BuyMailController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard/{page?}',[BuyMailController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/{page}', [BuyMailController::class, 'applyKey'])->name('apply_key');

    Route::post('/dashboard/unlimitmail/buymail', [BuyMailController::class, 'buyMailUnlimit'])->name('buy_unlimitmail');
});

require __DIR__.'/auth.php';
