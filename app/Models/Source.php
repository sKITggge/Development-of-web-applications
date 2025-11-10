<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Source extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sources';
    
    protected $fillable = [
        'url',
        'title',
        'logo',
        'logo_width',
        'logo_height',
        'published',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class, 'source_id', '_id');
    }
}
