<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Flobbos\PageComposer\Models\Concerns\HasPrefixedTable;

class CategoryTranslation extends Model
{
    use HasPrefixedTable;
    use HasFactory;

    protected $fillable = [
        'language_id',
        'category_id',
        'name'
    ];
}
