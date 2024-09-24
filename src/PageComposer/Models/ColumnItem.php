<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function PHPSTORM_META\map;

class ColumnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'column_id',
        'element_id',
        'sorting',
        'attributes',
        'active',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
        'attributes' => 'array'
    ];

    public function column()
    {
        return $this->belongsTo(Column::class);
    }

    public function element()
    {
        return $this->belongsTo(Element::class);
    }
}
