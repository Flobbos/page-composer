<div class="page-display">
    <div class="mx-4 lg:mx-auto max-w-7xl">
        @foreach ($page->rows as $row)
            <div class="grid grid-cols-12 gap-4 mb-4">
                @foreach ($row->columns as $column)
                    <div class="xl:col-span-{{ $column->column_size }} md:col-span-6 col-span-12 space-y-4">
                        @foreach ($column->column_items as $item)
                            <x-dynamic-component :component="'page-composer.elements.' . $item->element->component" :content="$item->content" />
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
    <div class="hidden col-span-3 col-span-6 col-span-12 xl:col-span-3 xl:col-span-6 xl:col-span-12"></div>
</div>
