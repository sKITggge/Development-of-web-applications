<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use \App\Models\Category;

class Post extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'posts';
    
    protected $fillable = [
        'title', 
        'link', 
        'description', 
        'pubDate', 
        'guid', 
        'source_id', 
        'category_ids',
        'image'
    ];

    public function source()
    {
        return $this->belongsTo(Source::class, 'source_id', '_id');
    }

    public function getCategoryNamesAttribute()
    {
        if (empty($this->category_ids)) return [];
        return Category::whereIn('_id', $this->category_ids)->pluck('name')->all();
    } 
}