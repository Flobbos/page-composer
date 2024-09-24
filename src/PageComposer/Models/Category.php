<?php

namespace Flobbos\PageComposer\Models;

use Flobbos\TranslatableDB\TranslatableDB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, TranslatableDB;

    public $translatedAttributes = ['name'];
}
