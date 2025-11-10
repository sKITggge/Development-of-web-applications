<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\News\RssFetcher;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Models\Category;
use App\Models\Source;

class RssImportController extends Controller
{
    public function import(Request $request): JsonResponse
    {
        $request->validate(['url' => 'required|url']);
        $url = $request->input('url');

        try {
            $fetcher = new RssFetcher($url);
            $items = $fetcher->fetch();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch RSS: '.$e->getMessage()], 422);
        }

        if (empty($items)) {
            return response()->json(['imported' => 0, 'message' => 'No items found']);
        }

        $upsertedCount = 0;
        
        foreach ($items as $it) {
            if (empty($it['source_id'])) {
                return response()->json(['error' => 'Source not recognized for url'], 422);
            }
            
            if (empty($it['guid'])) continue;

            $categoryIds = [];

            if (!empty($it['categories'])) {
                foreach ($it['categories'] as $categoryName) {
                    $category = Category::firstOrCreate(['name'=> $categoryName]);
                    $categoryIds[] = $category->id;
                }
            }
            
            Post::updateOrCreate(
                ['guid' => $it['guid']],
                [
                    'title' => $it['title'],
                    'link' => $it['link'],
                    'description' => $it['description'],
                    'pubDate' => $it['pubDate'],
                    'source_id' => $it['source_id'],
                    'category_ids' => $categoryIds,
                    'image' => $it['image'],
                ]
            );
            $upsertedCount++;
        }

        return response()->json([
            'imported_upserted' => $upsertedCount,
            'source' => $url,
            'items_processed' => count($items)
        ]);
    }
}