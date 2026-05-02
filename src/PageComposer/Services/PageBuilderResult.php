<?php

namespace Flobbos\PageComposer\Services;

use Flobbos\PageComposer\Models\Page;

final readonly class PageBuilderResult
{
    public function __construct(
        public Page $page,
        public array $rows,
    ) {}
}
