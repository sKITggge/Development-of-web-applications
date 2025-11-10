<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Source;

class ModeratorSourceController extends Controller
{
    public function index()
    {
        $sources = Source::where("published", false)->orderBy("created_at","desc")->get();

        return response()->json($sources);
    }

    public function update(Request $request, string $id)
    {
        $source = Source::find($id);

        if (! $source) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $data = $request->validate([
            'url' => 'required|url',
            'title' => 'required|string|max:255',
            'logo' => 'nullable|url',
            'logo_width' => 'nullable|integer|min:0',
            'logo_height' => 'nullable|integer|min:0',
        ]);

        $data['published'] = true;

        $source->update($data);

        return response()->json(['message'=> 'Source approved successfully'], 200);
    }

    public function destroy($id)
    {
        $source = Source::find($id);

        if (! $source) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $source->delete();
        
        return response()->json(null, 204);
    }
}
