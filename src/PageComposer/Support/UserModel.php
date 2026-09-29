<?php

namespace Flobbos\PageComposer\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * The app's user model, as configured for the default auth provider,
 * instead of assuming App\Models\User.
 */
class UserModel
{
    /**
     * @return class-string<Model>
     */
    public static function class(): string
    {
        return config('auth.providers.users.model', 'App\\Models\\User');
    }

    public static function find(mixed $id): ?Model
    {
        return blank($id) ? null : static::class()::find($id);
    }
}
