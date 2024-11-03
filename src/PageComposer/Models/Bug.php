<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Bug extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'type',
        'viewed',
        'resolved',
        'title',
        'description',
        'user_id',
        'photo'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
