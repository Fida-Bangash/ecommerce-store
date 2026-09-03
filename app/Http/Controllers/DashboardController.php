<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard (Spark Admin template).
     */
    public function index(Request $request)
    {
        return view('dashboard');
    }
}
