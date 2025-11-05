<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::orderBy('name')->get();
        
        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
            ]
        ]);

        $category = Category::create($validated);
        
        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $category = Category::find($id);
        
        if (!$category) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }
        
        return response()->json($category);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::find($id);
        
        if (!$category) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($id)
            ]
        ]);

        $category->update($validated);
        
        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }
}