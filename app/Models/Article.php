<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Article extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'content',
        'description',
        'url',
        'image_url',
        'source_name',
        'source_id',
        'category',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];


    public function scopeBySource(Builder $query, $source)
    {
        return $query->where('source_name', $source);
    }

    public function scopeByCategory(Builder $query, $category)
    {
        return $query->where('category', $category);
    }


    public function scopeByDateRange(Builder $query, $from = null, $to = null)
    {
        if ($from) {
            $query->where('published_at', '>=', Carbon::parse($from)->startOfDay());
        }

        if ($to) {
            $query->where('published_at', '<=', Carbon::parse($to)->endOfDay());
        }

        return $query;
    }


    public function scopeLatestByPublished(Builder $query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    public function scopeAvailableSources(Builder $query)
    {
        return $query->select('source_name')
                     ->distinct()
                     ->orderBy('source_name');
    }

    public function scopeAvailableCategories(Builder $query)
    {
        return $query->select('category')
                     ->whereNotNull('category')
                     ->distinct()
                     ->orderBy('category');
    }

    public function scopeUrl(Builder $query, $url)
    {
        return $query->where('url', $url);
    }
}
