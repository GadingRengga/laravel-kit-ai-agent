<?php

use App\Http\Controllers\AI\AiChatController;
use App\Http\Controllers\AI\AiConnectionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route AI — require dari routes/web.php:
|   require __DIR__.'/ai.php';
| di dalam group middleware('auth') yang sudah ada.
*/

Route::middleware(['auth', 'throttle:ai-chat'])->prefix('ai')->name('ai.')->group(function () {
    Route::get('/chat', [AiChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/new', [AiChatController::class, 'newConversation'])->name('chat.new');
    Route::post('/conversations/{conversation}/messages', [AiChatController::class, 'store'])->name('chat.store');
    Route::delete('/chat/{conversation}', [AiChatController::class, 'destroyConversation'])->name('chat.destroy');

    Route::post('/action-logs/{actionLog}/confirm', [AiChatController::class, 'confirmToolAction'])->name('action.confirm');
    Route::post('/action-logs/{actionLog}/reject', [AiChatController::class, 'rejectToolAction'])->name('action.reject');

    Route::post('/connections', [AiConnectionController::class, 'store'])->name('connections.store');
    Route::delete('/connections/{connection}', [AiConnectionController::class, 'destroy'])->name('connections.destroy');
});
