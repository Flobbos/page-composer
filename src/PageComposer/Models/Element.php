<?php

namespace Flobbos\PageComposer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Flobbos\PageComposer\Models\Concerns\HasPrefixedTable;
use Flobbos\PageComposer\Services\ContentSanitizer;

class Element extends Model
{
    use HasPrefixedTable;
    use HasFactory;

    protected $fillable = [
        'name',
        'component',
        'icon',
    ];

    /**
     * Icons are raw SVG rendered unescaped, so they're cleaned on the way in
     * and, for rows saved before this existed, on the way out.
     */
    protected function icon(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => app(ContentSanitizer::class)->svg($value),
            set: fn(?string $value) => app(ContentSanitizer::class)->svg($value),
        );
    }
}
