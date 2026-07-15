<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Superuser\MenuController;
use App\Http\Controllers\Superuser\PermissionController;
use App\Http\Controllers\Superuser\RoleController;
use App\Http\Controllers\Superuser\UserController;
use App\Http\Controllers\Superuser\UserRoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest routes (belum login)
|--------------------------------------------------------------------------
| Tidak ada route register karena akun dibuat lewat seeder/admin,
| bukan pendaftaran mandiri.
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // AI AGENT
    require __DIR__ . '/ai.php';
    require __DIR__ . '/ai-widget.php';
    // END AI AGENT

    // SUPERUSER
    Route::get('/superuser/menu', [MenuController::class, 'index'])
        ->name('superuser.menu.index');

    Route::get('/superuser/role', [RoleController::class, 'index'])
        ->name('superuser.role.index');

    Route::get('/superuser/permission', [PermissionController::class, 'index'])
        ->name('superuser.permission.index');

    Route::get('/superuser/user', [UserController::class, 'index'])
        ->name('superuser.user.index');

    Route::get('/superuser/user-role', [UserRoleController::class, 'index'])
        ->name('superuser.user-role.index');
    // END SUPERUSER
});

Route::get('/', function () {
    return redirect()->route(\Illuminate\Support\Facades\Auth::check() ? 'dashboard' : 'login');
});
