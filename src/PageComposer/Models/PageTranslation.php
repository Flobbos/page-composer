<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Flobbos\PageComposer\Models\Concerns\HasPrefixedTable;

class PageTranslation extends Model
{
    use HasPrefixedTable;
    use HasFactory;

    protected $fillable = [
        'page_id',
        'language_id',
        'content',
        'slug',
    ];

    protected $casts = [
        'content' => 'array'
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
