<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PageTemplate extends Model
{
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
        $userModel = config('auth.providers.users.model');
        return $this->belongsTo($userModel);
    }
}
