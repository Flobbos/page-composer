<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Model;
use Flobbos\TranslatableDB\TranslatableDB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory;
    use TranslatableDB;
    use SoftDeletes;

    public $translatedAttributes = [
        'content',
        'slug'
    ];

    protected $fillable = [
        'name',
        'photo',
        'active',
        'is_published',
        'published_on'
    ];

    protected $casts = [
        'published_on' => 'datetime'
    ];

    public function rows()
    {
        return $this->hasMany(Row::class)->orderBy('sorting')->where('active', true);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function translation()
    {
        return $this->hasOne(PageTranslation::class);
    }
}
