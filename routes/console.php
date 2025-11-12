<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

use App\Models\Source;
use App\Services\RssImport;

Schedule::call(function () {
    $importService = new RssImport();
    $sources = Source::where('published', true)->get();

    foreach ($sources as $source) {
        try {
            $imported = $importService->import($source);
        } catch (\Exception $e) {
            Log::error("Failed to import from source {$source->id}: {$e->getMessage()}");
        }
    }
})->hourly();