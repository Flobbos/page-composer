<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Model;
use Flobbos\PageComposer\Models\Concerns\HasPrefixedTable;
use Flobbos\TranslatableDB\TranslatableDB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Flobbos\PageComposer\Models\PageTranslation;
use Flobbos\PageComposer\Models\Tag;
use Flobbos\PageComposer\Models\Category;

class Page extends Model
{
    use HasPrefixedTable;
    use HasFactory;
    use TranslatableDB;
    use SoftDeletes;

    protected $translationModel = \Flobbos\PageComposer\Models\PageTranslation::class;

    public $translatedAttributes = [
        'content',
        'slug'
    ];

    protected $fillable = [
        'name',
        'category_id',
        'photo',
        'newsletter_image',
        'slider_image',
        'is_published',
        'published_on'
    ];

    protected $casts = [
        'published_on' => 'datetime'
    ];

    public function rows()
    {
        return $this->hasMany(Row::class)->orderBy('sorting');
    }

    public function active_rows()
    {
        return $this->hasMany(Row::class)->orderBy('sorting')->where('active', true);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, static::tablePrefix() . 'page_tag');
    }

    public function translation()
    {
        return $this->hasOne(PageTranslation::class);
    }
}
