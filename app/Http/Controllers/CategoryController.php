<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
 // Create a new category
public function create(Request $request)
{
    $categories = $request->all(); // get the array of categories

    $inserted = [];

    foreach ($categories as $categoryData) {
        $validated = validator($categoryData, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|url',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validated->errors()
            ], 422);
        }

        $category = Category::create($categoryData);
        $inserted[] = $category;
    }

    return response()->json([
        'message' => 'Categories created successfully',
        'data' => $inserted
    ], 201);
}
    //  Get all categories
    public function getAll()
    {
        $categories = Category::all();

        return response()->json($categories);
    }

    //  Get category by ID
    public function getById($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category);
    }

    //  Update category by ID
    public function updateById(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

    //  Delete category by ID
    public function deleteById($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
