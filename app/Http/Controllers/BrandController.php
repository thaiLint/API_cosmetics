<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
 
    public function create(Request $request)
    {
        $brandsData = $request->all(); 

       
        if (isset($brandsData['name_brand'])) {
            $brandsData = [$brandsData]; 
        }

        $createdBrands = [];

        foreach ($brandsData as $data) {
            $validated = validator($data, [
                'name_brand' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image_brand' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
            ])->validate();

            $createdBrands[] = Brand::create($validated);
        }

        return response()->json([
            'message' => 'Brands created successfully',
            'data' => $createdBrands
        ]);
    }

 
    public function getAll()
    {
        $brands = Brand::all();
        return response()->json($brands);
    }

 
    public function getById($id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        return response()->json($brand);
    }

   
    public function updateById(Request $request, $id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        $validated = $request->validate([
            'name_brand' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'image_brand' => 'nullable|string',
            'category_id' => 'sometimes|required|exists:categories,id',
        ]);

        $brand->update($validated);

        return response()->json([
            'message' => 'Brand updated successfully',
            'data' => $brand
        ]);
    }

    
    public function deleteById($id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        $brand->delete();

        return response()->json([
            'message' => 'Brand deleted successfully'
        ]);
    }
}
