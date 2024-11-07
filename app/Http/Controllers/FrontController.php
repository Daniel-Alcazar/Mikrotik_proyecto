<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function showLogin()
    {
        return view('loginRouter');
    }

    public function showDashboard()
    {
        if (session('authenticated')) {
            return view('dashboard');
        } else {
            return redirect()->route('login.show');
        }
    }

}