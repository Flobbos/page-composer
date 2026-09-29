<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Flobbos\PageComposer\Models\Concerns\HasPrefixedTable;

class Column extends Model
{
    use HasPrefixedTable;
    use HasFactory;

    protected $fillable = [
        'row_id',
        'column_size',
        'attributes',
        'sorting',
        'active'
    ];

    protected $casts = [
        'attributes' => 'array',
        'active' => 'bool',
    ];

    public function column_items()
    {
        return $this->hasMany(ColumnItem::class)->orderBy('sorting');
    }

    public function active_column_items()
    {
        return $this->hasMany(ColumnItem::class)->orderBy('sorting')->where('active', true);
    }
}
