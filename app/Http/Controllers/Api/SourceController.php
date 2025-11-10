<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Source;
use App\Models\Category;

class SourceController extends Controller
{
    public function index()
    {
        $sources = Source::where('published', true)->orderBy('title')->get();

        $result = $sources->map(function ($source) {
            return [
                'id' => $source->_id,
                'url' => $source->url,
                'title' => $source->title,
                'logo' => $source->logo,
                'logo_width' => $source->logo_width,
                'logo_height' => $source->logo_height,
                'created_at' => $source->created_at,
            ];
        });

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'url' => 'required|url',
            'title' => 'required|string|max:255',
            'logo' => 'nullable|url',
            'logo_width' => 'nullable|integer|min:0',
            'logo_height' => 'nullable|integer|min:0',
        ]);

        $data['published'] = false;

        $source = Source::firstOrCreate(['url' => $data['url']], $data);

        return response()->json($source, $source->wasRecentlyCreated ? 201 : 200);
    }

    public function show($id)
    {
        $source = Source::find($id);
        
        if (! $source) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        return response()->json([
            'id' => $source->_id,
            'url' => $source->url,
            'title' => $source->title,
            'logo' => $source->logo,
            'logo_width' => $source->logo_width,
            'logo_height' => $source->logo_height,
            'category_ids' => $categoryIds,
        ]);
    }
}