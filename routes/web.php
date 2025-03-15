<?php

use App\Http\Controllers\BuyMailController;
use App\Http\Controllers\FacebookUidController;
use App\Http\Controllers\FacebookUserController;
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

    // UID
    Route::get('/facebook-uid', [FacebookUidController::class, 'index'])->name('facebook_uid.index');
    Route::post('/facebook-uid', [FacebookUidController::class, 'getfacebookUid'])->name('facebook_uid.post');

    // Google 2FA
    Route::get('/google2fa', [FacebookUidController::class, 'google2FA'])->name('google_2fa');

    // Facebook data
    Route::get('/list-facebook-user', [FacebookUserController::class, 'index'])->name('fb_user.list');
    Route::get('/list-facebook-user/export-csv', [FacebookUserController::class, 'export'])->name('fb_user.export');
    Route::post('/list-facebook-user/import-csv', [FacebookUserController::class, 'import'])->name('fb_user.import');
    Route::delete('/list-facebook-user/delete', [FacebookUserController::class, 'delete'])->name('fb_user.delete');
    Route::get('/get-info', [FacebookUserController::class, 'getInfo'])->name('fb_user.get_info');
    Route::get('/fetch-info', [FacebookUserController::class, 'fetchInfo'])->name('fb_user.fetch_info');
    Route::put('/save-info', [FacebookUserController::class, 'saveInfo'])->name('fb_user.save_info');

});

require __DIR__.'/auth.php';
