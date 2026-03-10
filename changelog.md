## Version History

### v. 0.0.20

-   **Mini Map UI Tuning**: Refined mini map row presentation for dense content pages
    -   Reduced row/card spacing to improve readability with many rows
    -   Kept drag handle placement reliable within row boundaries
    -   Tightened per-column preview details to reduce visual noise

### v. 0.0.19

-   **Element Generator Fix**: `page-composer:element` now also creates the preview Blade component
    -   Added preview stub generation alongside class and Livewire view generation
    -   Preview files are now created in `resources/views/components/page-composer-elements/`
-   **Element Registration Validation**: Manual element registration now validates preview file presence
    -   Validation now checks class file, Livewire view file, and preview Blade file before saving
    -   Added clear validation error message when preview file is missing
-   **Path Alignment**: Unified preview component paths across creation and validation flows
    -   Generator output path and admin validation path now point to the same directory

### v. 0.0.18

-   **Element Component Registration**: Enhanced manual component registration workflow
    -   Added "Component Name" input field when skipping template generation
    -   Users can now provide custom component directory name for existing custom components
    -   Added server-side file path validation to ensure both class and view files exist
    -   Clear error messages showing expected file paths when validation fails
-   **Search Performance**: Improved search efficiency
    -   Implemented minimum 4-character threshold via `updatedSearch()` lifecycle hook
    -   Searches with 1-3 characters are now skipped (no database query triggered)
    -   Clearing search (empty string) still shows full list
    -   Reduces query churn during user typing
-   **User Confirmations**: Added protection against accidental destructive actions
    -   Added confirmation prompt before deleting bug reports
    -   Added confirmation prompt before deleting templates
    -   Page deletion already had confirmation modals in place
-   **Fixed**: Cleaned up duplicate "Version History" header in changelog

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
-   **Bug Tracker Enhancement**: Added support for multiple screenshots per bug report
    -   Users can now upload up to 5 screenshots (2MB each) when creating/viewing bug reports
    -   Multiple uploads supported across repeated file picker selections
    -   Preview tiles with remove buttons before save
    -   Maintains backward compatibility with existing single-photo bug entries
    -   Added JSON `photos` column to store multiple filenames
-   **Element Management**: Made template generation optional during element creation
    -   Added "Create element from template?" checkbox to element creation form
    -   Artisan command only runs if checkbox is checked
    -   Users can register elements without auto-generating component/view files
-   **Element Renaming**: Fixed component file renaming
    -   Replaced non-existent `livewire:move` command with PHP `rename()` function
    -   Properly renames both class file and blade view file when updating element name
-   **Filter Reset**: Fixed filter reset functionality on PageIndex
    -   Changed from manual null assignment to using Livewire's `reset()` method
    -   Properly clears URL parameter from query string
    -   Added proper prop passing to filter component (Blade component)
-   **Search Improvements**: Added `trim()` to search input
    -   Search now works correctly with leading/trailing spaces
    -   Prevents empty-space searches from matching unintended results
-   **Fixed**: Pagination serialization error - removed public `$pages` property, paginator now only returned from render()
-   **Fixed**: Element data persistence - ensured all element components have `$target` property and dispatch `elementUpdated` events for parent synchronization
-   **Fixed**: "pagebuilder:element" command name reference to actual "page-composer:element" in ElementComponent
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
