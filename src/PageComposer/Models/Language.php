<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Flobbos\PageComposer\Models\Concerns\HasPrefixedTable;

class Language extends Model
{
    use HasPrefixedTable;
    use HasFactory;

    protected $fillable = [
        'name',
        'locale'
    ];
}
