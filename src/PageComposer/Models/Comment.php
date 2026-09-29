<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Flobbos\PageComposer\Models\Concerns\HasPrefixedTable;
use Flobbos\PageComposer\Support\UserModel;

class Comment extends Model
{
    use HasPrefixedTable;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bug_id',
        'content'
    ];

    public function user()
    {
        return $this->belongsTo(UserModel::class());
    }

    public function bug()
    {
        return $this->belongsTo(Bug::class);
    }
}
