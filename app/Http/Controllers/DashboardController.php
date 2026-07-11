<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;


class DashboardController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $menus = [];

        if ($user && method_exists($user, 'getMenus')) {
            $menus = $user->getMenus();
        }

        return view('dashboard', compact('user', 'menus'));
    }
}
