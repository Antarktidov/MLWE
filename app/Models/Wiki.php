<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Article;

class Wiki extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = false;

    public function articles()
    {
        return $this->hasMany(Article::class, 'wiki_id', 'id');
    }

    public function scopeActive($q) {
        return $q->whereNull('deleted_at');
    }

    public function scopeByUrl($q, $url) {
        return $q->where('url', $url);
    }
}
