<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'translated_title',
        'director',
        'writer',
        'actors',
        'year',
        'release_date',
        'country',
        'language',
        'runtime',
        'genre',
        'rating',
        'imdb_rating',
        'imdb_link',
        'douban_link',
        'poster_url',
        'description',
        'awards',
        'screenshots',
        'target_audience',
        'source_organization',
        'is_published',
    ];

    protected $casts = [
        'year' => 'integer',
        'rating' => 'decimal:1',
        'screenshots' => 'array',
        'is_published' => 'boolean',
    ];

    /**
     * 公开接口只允许访问已上架影片；未找到/已下架均返回 404，
     * 避免对外暴露影片是否存在。
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}