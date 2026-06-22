<?php

namespace Flobbos\PageComposer\Tests\Fixtures;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Minimal User stub used by tests that interact with code paths referencing
 * App\Models\User (e.g. BugComponent, CommentComponent). Matches the default
 * Laravel users table shape from the framework migrations.
 */
class User extends Authenticatable implements AuthenticatableContract
{
    use HasFactory;
    use Notifiable;

    protected $table = 'users';

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];
}
