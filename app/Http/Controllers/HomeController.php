<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the public storefront landing page (Spark Free template).
     */
    public function index(Request $request)
    {
        return view('home');
    }
}
