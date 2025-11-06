<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Post::with(['source']);
        
        if ($request->has('categories')) {
            $categoryIds = is_array($request->categories) 
                ? $request->categories 
                : [$request->categories];

            $query->whereIn('category_ids', $categoryIds);
        }

        if ($request->has('sources')) {
            $sourceIds = is_array($request->sources) 
                ? $request->sources 
                : [$request->sources];

            $query->whereIn('source_id', $sourceIds);
        }
        
        $posts = $query->orderBy('pubDate', 'desc')->get();
        
        $result = $posts->map(function($post) {
            return [
                'id' => $post->_id,
                'title' => $post->title,
                'link' => $post->link,
                'description' => $post->description,
                'pubDate' => $post->pubDate,
                'guid' => $post->guid,
                'source' => $post->source ? $post->source->title : '',
                'categories' => $post->category_names,
                'image' => $post->image,
            ];
        });
        
        return response()->json($result);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "title" => "required|max:255",
            "link" => "required|string",
            "description" => "nullable|string",
            "pubDate" => "nullable|date",
            "guid" => "required|string|unique:posts,guid",
            "source_id" => "nullable|exists:sources,_id",
            "category_ids" => "nullable|array",
            "category_ids.*" => "exists:categories,_id"
        ]);

        $post = Post::create($validated);
        
        return response()->json($post, 201);
    }

    public function show(string $id): JsonResponse
    {
        $post = Post::with(['source'])->find($id);
        
        if (!$post) {
            return response()->json(["message" => "Post not found"], 404);
        }
        
        return response()->json([
            'id' => $post->_id,
            'title' => $post->title,
            'link' => $post->link,
            'description' => $post->description,
            'pubDate' => $post->pubDate,
            'guid' => $post->guid,
            'source' => $post->source,
            'categories' => $post->category_names,
            'image'=> $post->image,
        ]);
    }
}