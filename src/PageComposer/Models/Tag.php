<?php

namespace Flobbos\PageComposer\Models;

use Flobbos\TranslatableDB\TranslatableDB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Flobbos\PageComposer\Models\Concerns\HasPrefixedTable;

class Tag extends Model
{
    use HasPrefixedTable;
    use HasFactory, TranslatableDB;

    public $translatedAttributes = ['name'];

    protected $fillable = ['active'];
}
