<?php

namespace App\Services\News;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use Exception;

class RssFetcher
{
    protected string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function fetch(): array
    {
        $resp = Http::get($this->url);

        if (!$resp->successful()) {
            throw new Exception('Failed to fetch RSS: ' . $resp->status());
        }

        $body = $resp->body();

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body, SimpleXMLElement::class, LIBXML_NOCDATA);
        
        if ($xml === false) {
            $err = collect(libxml_get_errors())->pluck('message')->join('; ');
            libxml_clear_errors();
            throw new Exception('Invalid XML: ' . $err);
        }

        $source = $this->getSourceId($this->url);

        $items = [];
        if (isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $items[] = $this->normalizeRssItem($item, $source);
            }
        } elseif (isset($xml->entry)) {
            foreach ($xml->entry as $entry) {
                $items[] = $this->normalizeAtomEntry($entry, $source);
            }
        }

        return $items;
    }

    protected function getSourceId(string $url): string
    {
        $db = DB::connection('mongodb');
        $collection = $db->selectCollection('sources');

        $source = $collection->findOne(['url' => $url]);

        if (! $source) {
            return '';
        }

        return (string) $source->_id;
    }

    protected function normalizeRssItem(SimpleXMLElement $item, string $source_id): array
    {
        $categories = [];
        if (isset($item->category)) {
            foreach ($item->category as $cat) {
                $categories[] = (string)$cat;
            }
        }
        return [
            'title' => (string) ($item->title ?? ''),
            'link' => (string) ($item->link ?? ''),
            'description' => (string) ($item->description ?? ''),
            'pubDate' => $this->parseDate((string) ($item->pubDate ?? '')),
            'guid' => (string) ($item->guid ?? $item->link ?? ''),
            'source_id' => $source_id,
            'categories' => $categories,
        ];
    }

    protected function normalizeAtomEntry(SimpleXMLElement $entry, string $source_id): array
    {
        $link = '';
        if (isset($entry->link)) {
            foreach ($entry->link as $l) {
                $attrs = $l->attributes();
                if (isset($attrs['href'])) { $link = (string) $attrs['href']; break; }
            }
        }
        $categories = [];
        if (isset($entry->category)) {
            foreach ($entry->category as $cat) {
                $attrs = $cat->attributes();
                if (isset($attrs['term'])) {
                    $categories[] = (string)$attrs['term'];
                } elseif ((string)$cat) {
                    $categories[] = (string)$cat;
                }
            }
        }
        return [
            'title' => (string) ($entry->title ?? ''),
            'link' => $link,
            'description' => (string) ($entry->summary ?? $entry->content ?? ''),
            'pubDate' => $this->parseDate((string) ($entry->updated ?? $entry->published ?? '')),
            'guid' => (string) ($entry->id ?? $link ?? ''),
            'source_id' => $source_id,
            'categories' => $categories,
        ];
    }

    protected function parseDate(string $date): ?string
    {
        if (empty($date)) return null;
        try {
            return (new \DateTime($date))->format(\DateTime::ATOM);
        } catch (\Exception $e) {
            return null;
        }
    }
}
