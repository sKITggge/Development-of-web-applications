<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Support\Facades\Log;

class RssImport
{
    public function import(Source $source)
    {
        try {
            $fetcher = new RssFetcher($source->url, $source->id);
            $items = $fetcher->fetch();
        } catch (\Exception $e) {
            Log::error('Failed to fetch RSS', [
                'source_id' => $source->id,
                'url' => $source->url,
                'error' => $e->getMessage()
            ]);
            return 0;
        }

        if (empty($items)) {
            return 0;
        }

        $upsertedCount = 0;
        
        foreach ($items as $it) {
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

        return $upsertedCount;
    }
}