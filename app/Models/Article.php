<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Comment;
use App\Models\Revision;

class Article extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = false;

    public function wiki()
    {
        return $this->belongsTo(Wiki::class, 'wiki_id', 'id');
    }

    public function revisions()
    {
        return $this->hasMany(Revision::class, 'article_id', 'id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'article_id', 'id');
    }

    public function scopeActive($q) {
        return $q->whereNull('deleted_at');
    }

    public function scopeByWiki($q, Wiki $wiki) {
        return $q->where('wiki_id', $wiki->id);
    }

    public function scopeByUrl($q, $url) {
        return $q->where('url_title', $url);
    }

    public function scopeByNS($q, $ns) {
        return $q->where('namespace', $ns);
    }

}
