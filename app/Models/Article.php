<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'url_to_image',
        'source_name',
        'source_id',
        'author',
        'category',
        'country',
        'language',
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

    /**
     * Scope to filter articles by source.
     */
    public function scopeBySource($query, $source)
    {
        return $query->where('source_name', $source);
    }

    /**
     * Scope to filter articles by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter articles by date range.
     */
    public function scopeByDateRange($query, $from = null, $to = null)
    {
        if ($from) {
            $query->where('published_at', '>=', Carbon::parse($from));
        }
        
        if ($to) {
            $query->where('published_at', '<=', Carbon::parse($to));
        }

        return $query;
    }

    /**
     * Scope to order articles by publication date (newest first).
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('published_at', 'desc');
    }
}
