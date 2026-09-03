<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the public storefront landing page with real, dynamic data.
     */
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->active()
            ->orderBy('name')
            ->get();

        $trendingProducts = Product::query()
            ->with(['images', 'category', 'variants'])
            ->active()
            ->latest()
            ->take(8)
            ->get();

        return view('home', compact('categories', 'trendingProducts'));
    }

    /**
     * Show the full product catalog, with optional search and category
     * filtering.
     */
    public function shop(Request $request): View
    {
        $categories = Category::query()->active()->orderBy('name')->get();

        $products = Product::query()
            ->with(['images', 'category', 'variants'])
            ->active()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->string('search').'%');
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category_id', $request->integer('category'));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('shop', compact('products', 'categories'));
    }
}
