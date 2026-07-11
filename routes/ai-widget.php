<?php

use App\Http\Controllers\AI\AiWidgetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route AI Widget (tombol mengambang di semua halaman) — require dari
| routes/web.php, PERSIS di bawah require routes/ai.php:
|
|   require __DIR__.'/ai.php';
|   require __DIR__.'/ai-widget.php';
|
| Pakai limiter 'ai-chat' yang sama dengan routes/ai.php supaya kena
| rate-limit yang sama juga.
*/

Route::middleware(['auth', 'throttle:ai-chat'])->prefix('ai/widget')->name('ai.widget.')->group(function () {
    Route::get('/state', [AiWidgetController::class, 'state'])->name('state');
    Route::post('/connect', [AiWidgetController::class, 'connect'])->name('connect');
    Route::delete('/disconnect', [AiWidgetController::class, 'disconnect'])->name('disconnect');
    Route::post('/messages', [AiWidgetController::class, 'send'])->name('send');
    Route::post('/new', [AiWidgetController::class, 'newConversation'])->name('new');
});
