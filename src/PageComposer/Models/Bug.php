<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'photo',
        'photos',
    ];

    protected $casts = [
        'photos' => 'array',
    ];

    public function getAttachmentPathsAttribute(): array
    {
        $photos = $this->photos ?? [];

        if (empty($photos) && $this->photo) {
            return [$this->photo];
        }

        return $photos;
    }

    public function user()
    {
        $userModel = config('auth.providers.users.model');
        return $this->belongsTo($userModel);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
