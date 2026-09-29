<?php

namespace Flobbos\PageComposer\Models\Concerns;

/**
 * Prefixes the model's table with pagecomposer.table_prefix, so package
 * tables (rows, columns, tags...) can't collide with the app's.
 */
trait HasPrefixedTable
{
    public static function tablePrefix(): string
    {
        return (string) config('pagecomposer.table_prefix', 'pc_');
    }

    public function getTable()
    {
        // Eloquent copies the resolved name onto new instances via
        // setTable(), so an explicit table is already prefixed.
        return $this->table ?? static::tablePrefix() . parent::getTable();
    }
}
