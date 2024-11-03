<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bug_id',
        'content'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bug()
    {
        return $this->belongsTo(Bug::class);
    }
}
