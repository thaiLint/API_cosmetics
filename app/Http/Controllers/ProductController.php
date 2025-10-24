<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class ProductController extends Controller
{
   // Insert multiple products
    public function create(Request $request)
    {
        $products = $request->all();

        foreach ($products as $data) {

            // Validate each product
            $validated = Validator::make($data, [
                'name' => 'required|string',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'qty' => 'required|integer',
                'category' => 'required|string',    // temporary category name
                'images' => 'required|array',
                'images.*' => 'required|url',
            ])->validate();

            // Create product
            Products::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'qty' => $validated['qty'],
                'category' => $validated['category'], // store as string for now
                'images' => $validated['images'],
                'image' => $validated['images'][0] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'Products created successfully'
        ]);
    }

    // Get all products
    public function getAll()
    {
        $products = Products::all();

        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'qty' => $product->qty,
                'category' => $product->category,       // temporary string
                'image' => $product->images[0] ?? null,
                'images' => $product->images,
            ];
        });

        return response()->json(['status' => 200, 'data' => $data]);
    }

    // Get product by ID
    public function getById($id)
    {
        $product = Products::find($id);

        if (!$product) {
            return response()->json(['status' => 404, 'message' => 'Product not found']);
        }

        return response()->json([
            'status' => 200,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'qty' => $product->qty,
                'category' => $product->category,
                'image' => $product->images[0] ?? null,
                'images' => $product->images,
            ]
        ]);
    }

    // Get products by category name
    public function getByCategory($category)
    {
        $products = Products::where('category', $category)->get();

        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'qty' => $product->qty,
                'category' => $product->category,
                'image' => $product->images[0] ?? null,
                'images' => $product->images,
            ];
        });

        return response()->json(['status' => 200, 'data' => $data]);
    }

    
    public function linkCategories()
    {
        $products = Products::all();

        foreach ($products as $product) {
            $category = Category::firstOrCreate(['name' => $product->category]);
            $product->category_id = $category->id;
            $product->save();
        }

        return response()->json(['message' => 'Products linked to categories successfully']);
    }
     public function search(Request $request)
{
    $query = $request->input('q'); // get search term from ?q=...

    if (!$query) {
        return response()->json([
            'status' => 400,
            'message' => 'Please provide a search term'
        ], 400);
    }

    $products = Products::where('name', 'like', "%{$query}%")->get();

    if ($products->isEmpty()) {
        return response()->json([
            'status' => 404,
            'message' => 'No products found'
        ], 404);
    }

    return response()->json([
        'status' => 200,
        'data' => $products
    ]);
}

}

