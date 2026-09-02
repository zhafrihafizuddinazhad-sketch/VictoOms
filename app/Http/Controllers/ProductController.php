<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $products = Product::query()

            ->when($search, function ($query) use ($search) {

                $query->where('product_name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");

            })

            ->orderBy('product_name')

            ->paginate(10)

            ->withQueryString();


        return view('products.index', compact(
            'products',
            'search'
        ));
    }


    public function create()
    {
        return view('products.create');
    }


    public function store(Request $request)
    {
        $validated = $request->validate([

            'product_name' =>
                'required|string|max:255',

            'category' =>
                'nullable|string|max:100',

            'default_price' =>
                'required|numeric|min:0',

            'description' =>
                'nullable|string',

        ]);


        $validated['is_active'] = true;


        Product::create($validated);


        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product created successfully.'
            );
    }


    public function edit(Product $product)
    {
        return view(
            'products.edit',
            compact('product')
        );
    }


    public function update(
        Request $request,
        Product $product
    ) {

        $validated = $request->validate([

            'product_name' =>
                'required|string|max:255',

            'category' =>
                'nullable|string|max:100',

            'default_price' =>
                'required|numeric|min:0',

            'description' =>
                'nullable|string',

        ]);


        $product->update($validated);


        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product updated successfully.'
            );
    }


    public function toggle(Product $product)
    {
        $product->update([

            'is_active' =>
                !$product->is_active,

        ]);


        return back()->with(
            'success',
            $product->is_active
                ? 'Product activated successfully.'
                : 'Product deactivated successfully.'
        );
    }
}