<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Row extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'language_id',
        'attributes',
        'available_space',
        'alignment',
        'expanded',
        'active',
        'sorting',
    ];

    protected $casts = [
        'attributes' => 'array'
    ];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function columns()
    {
        return $this->hasMany(Column::class)->orderBy('sorting')->where('active', true);
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
