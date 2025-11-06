<?php

namespace App\Services\News;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use Exception;

class RssFetcher
{
    private string $url;

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

    private function normalizeRssItem(SimpleXMLElement $item, string $source_id): array
    {
        $categories = [];

        if (isset($item->category)) {
            foreach ($item->category as $cat) {
                $categories[] = (string)$cat;
            }
        }

        $image_url = $this->getImageUrl($item);

        return [
            'title' => (string) ($item->title ?? ''),
            'link' => (string) ($item->link ?? ''),
            'description' => (string) ($item->description ?? ''),
            'pubDate' => $this->parseDate((string) ($item->pubDate ?? '')),
            'guid' => (string) ($item->guid ?? $item->link ?? ''),
            'source_id' => $source_id,
            'categories' => $categories,
            'image' => $image_url,
        ];
    }

    private function normalizeAtomEntry(SimpleXMLElement $entry, string $source_id): array
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

        $image_url = $this->getImageUrl($entry);

        return [
            'title' => (string) ($entry->title ?? ''),
            'link' => $link,
            'description' => (string) ($entry->summary ?? $entry->content ?? ''),
            'pubDate' => $this->parseDate((string) ($entry->updated ?? $entry->published ?? '')),
            'guid' => (string) ($entry->id ?? $link ?? ''),
            'source_id' => $source_id,
            'categories' => $categories,
            'image' => $image_url,
        ];
    }

    private function getSourceId(string $url): string
    {
        $db = DB::connection('mongodb');
        $collection = $db->selectCollection('sources');

        $source = $collection->findOne(['url' => $url]);

        if (! $source) {
            return '';
        }

        return (string) $source->_id;
    }

    private function getImageUrl(SimpleXMLElement $item): string
    {
        $media_content_image = $this->getMediaContentImage($item);
        if ($media_content_image) {
            return $media_content_image;
        }

        $media_thumbnail_image = $this->getMediaThumbnailImage($item);
        if ($media_thumbnail_image) {
            return $media_thumbnail_image;
        }

        $enclosureImage = $this->getEnclosureImage($item);
        if ($enclosureImage) {
            return $enclosureImage;
        }

        if (isset($item->image)) {
            return (string)$item->image;
        }

        return '';
    }

    private function getMediaContentImage($item) {
        $namespaces = $item->getNamespaces(true);
    
        if (isset($namespaces['media'])) {
            $media = $item->children($namespaces['media']);
            
            if (isset($media->content)) {
                $maxWidth = 0;
                $bestImageUrl = null;
            
                foreach ($media->content as $content) {
                    $attributes = $content->attributes();
                    $width = (int)$attributes->width;
                    $url = (string)$attributes->url;
                    
                    if ($width > $maxWidth) {
                        $maxWidth = $width;
                        $bestImageUrl = $url;
                    }
                }
            }
            
            return $bestImageUrl;
        }
    }

    private function getMediaThumbnailImage(SimpleXMLElement $item): ?string
    {
        $namespaces = $item->getNamespaces(true);

        if (isset($namespaces['media'])) {
            $media = $item->children($namespaces['media']);
            
            if (isset($media->thumbnail)) {
                $thumbnail = $media->thumbnail;
                $attributes = $thumbnail->attributes();
                return (string)$attributes->url;
            }
        }
        
        return null;
    }

    function getEnclosureImage($item) {
        if (isset($item->enclosure)) {
            $attributes = $item->enclosure->attributes();
            return $attributes->url;
        }
        return null;
    }

    private function parseDate(string $date): ?string
    {
        if (empty($date)) return null;
        try {
            return (new \DateTime($date))->format(\DateTime::ATOM);
        } catch (\Exception $e) {
            return null;
        }
    }
}
