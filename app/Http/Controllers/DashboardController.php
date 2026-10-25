<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isTenant()) {
            return redirect()->route('tenant.orders.index');
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.orders.index');
        }

        if ($user->isSuperAdmin()) {
            return redirect()->route('finance.index');
        }

        return view('dashboard');
    }
}
