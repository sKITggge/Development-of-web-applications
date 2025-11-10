<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use App\Models\PersonalAccessToken;

class User extends Authenticatable
{
    use Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'tracked_categories',
        'tracked_sources',
        'role'
    ];

    const USER_ROLE = 'user';
    const MODERATOR_ROLE = 'moderator';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'tracked_categories' => 'array',
            'tracked_sources' => 'array'
        ];
    }

    public function isModerator(): bool
    {
        return $this->role === $this::MODERATOR_ROLE;
    }

    public function isUser(): bool
    {
        return $this->role === $this::USER_ROLE;
    }
    
    public function tokens()
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    public function createToken(string $name)
    {
        $plainTextToken = Str::random(40);
        
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
        ]);

        return new class($token, $plainTextToken) {
            public function __construct(
                public $accessToken,
                public $plainTextToken
            ) {}

            public function plainTextToken()
            {
                return $this->accessToken->getKey() . '|' . $this->plainTextToken;
            }
        };
    }

    public function currentAccessToken()
    {
        return $this->accessToken;
    }

    public function withAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'user_categories', 'user_id', 'category_id')
                    ->withTimestamps();
    }

    public function sources()
    {
        return $this->belongsToMany(Source::class, 'user_sources', 'user_id', 'source_id')
                    ->withTimestamps();
    }

    public function addTrackedCategory($categoryId): bool
    {
        $current = $this->tracked_categories ?? [];
        
        if (!in_array($categoryId, $current)) {
            $current[] = $categoryId;
            $this->tracked_categories = $current;
            return $this->save();
        }
        
        return false;
    }

    public function removeTrackedCategory($categoryId): bool
    {
        $current = $this->tracked_categories ?? [];
        $updated = array_diff($current, [$categoryId]);
        
        if (count($current) !== count($updated)) {
            $this->tracked_categories = array_values($updated);
            return $this->save();
        }
        
        return false;
    }

    public function addTrackedSource($sourceId): bool
    {
        $current = $this->tracked_sources ?? [];
        
        if (!in_array($sourceId, $current)) {
            $current[] = $sourceId;
            $this->tracked_sources = $current;
            return $this->save();
        }
        
        return false;
    }

    public function removeTrackedSource($sourceId): bool
    {
        $current = $this->tracked_sources ?? [];
        $updated = array_diff($current, [$sourceId]);
        
        if (count($current) !== count($updated)) {
            $this->tracked_sources = array_values($updated);
            return $this->save();
        }
        
        return false;
    }
}