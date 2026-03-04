## Version History

### v. 0.0.17

-   Added 6 new out-of-the-box elements:
-   Hero/Banner (full-width background + overlay text + CTA)
-   Grid/Cards (services/features grid with icons/images)
-   Bullet List/Features (icon-based feature list)
-   Testimonials/Trust Badges (logos, badges, stats)
-   Accordion/FAQ (collapsible Q&A sections)
-   Call-to-Action Section (centered CTA block)
-   Added pagination to PageIndex component (15 items per page with WithPagination trait)
-   Added search functionality to PageIndex (searches ID, slug, name, title with live debounced search)
-   **Fixed**: Pagination serialization error - removed public `$pages` property, paginator now only returned from render()
-   **Fixed**: Element data persistence - ensured all element components have `$target` property and dispatch `elementUpdated` events for parent synchronization
-   **Performance**: Added `#[Computed]` attributes to `sortedElements()`, `sortedColumns()`, and `sortedRows()` for query caching
-   **Performance**: Replaced `uniqid()` with stable component keys (based on source/locale/position) to enable Livewire component diffing instead of full re-renders
-   Updated element generator stub to include `$target` property and event dispatch pattern

### v. 0.0.16

-   added ID field to rows

### v. 0.0.15

-   Fixed visual problem in the pages index
-   Fixed wrong redirect

### v. 0.0.14

-   Fixed a weird entangle issue
-   Fixed language component pop-under bug
-   Updated Readme with additional information

### v. 0.0.13

-   previous changes on github
-   fixed an issue with doubled rows on update
