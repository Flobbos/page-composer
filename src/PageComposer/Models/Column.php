<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Column extends Model
{
    use HasFactory;

    protected $fillable = [
        'row_id',
        'column_size',
        'attributes',
        'sorting',
        'active'
    ];

    protected $casts = [
        'attributes' => 'array'
    ];

    public function column_items()
    {
        return $this->hasMany(ColumnItem::class)->orderBy('sorting')->where('active', true);;
    }
}
