<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Flobbos\PageComposer\Models\Concerns\HasPrefixedTable;

class TagTranslation extends Model
{
    use HasPrefixedTable;
    use HasFactory;

    protected $fillable = [
        'name',
        'language_id',
        'tag_id'
    ];
}
