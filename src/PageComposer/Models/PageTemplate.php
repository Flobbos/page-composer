<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Flobbos\PageComposer\Models\Concerns\HasPrefixedTable;
use Flobbos\PageComposer\Support\UserModel;

class PageTemplate extends Model
{
    use HasPrefixedTable;
    use HasFactory;

    protected $fillable = [
        'name',
        'content',
        'languages',
        'user_id'
    ];

    protected $casts = [
        'content' => 'array',
        'languages' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(UserModel::class());
    }
}
