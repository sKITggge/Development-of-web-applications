<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Source;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'tracked_categories' => $user->tracked_categories ?? [],
            'tracked_sources' => $user->tracked_sources ?? [],
            'role' => $user->role,
            'created_at' => $user->created_at,
            ]
        );
    }

    public function getTrackedCategories(Request $request): JsonResponse
    {
        $user = $request->user();
        $categoryIds = $user->tracked_categories ?? [];
        
        if (empty($categoryIds)) {
            return response()->json(['categories' => []]);
        }
        
        $categories = Category::whereIn('_id', $categoryIds)->get();
        
        return response()->json([
            'categories' => $categories,
            'count' => $categories->count()
        ]);
    }

    public function updateTrackedCategories(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'category_ids' => 'array',
            'category_ids.*' => 'exists:categories,_id'
        ]);

        $categoryIds = $validated['category_ids'];
        
        $user->tracked_categories = $categoryIds;
        $user->save();
        
        return response()->json([
            'message' => 'Tracked categories updated successfully',
        ]);
    }

    public function getTrackedSources(Request $request): JsonResponse
    {
        $user = $request->user();
        $sourceIds = $user->tracked_sources ?? [];
        
        if (empty($sourceIds)) {
            return response()->json(['sources' => []]);
        }
        
        $sources = Source::whereIn('_id', $sourceIds)->get();
        
        return response()->json([
            'sources' => $sources,
            'count' => $sources->count()
        ]);
    }

    public function updateTrackedSources(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'source_ids' => 'array',
            'source_ids.*' => 'exists:sources,_id'
        ]);

        $sourceIds = $validated['source_ids'];
        
        $user->tracked_sources = $sourceIds;
        $user->save();
        
        return response()->json([
            'message' => 'Tracked sources updated successfully',
        ]);
    }
}