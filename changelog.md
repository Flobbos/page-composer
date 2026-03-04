## Version History

### v. 0.0.17

-   **[BREAKING]** Migrated to Livewire 3 reactive property system
-   Removed legacy event dispatch system (`elementUpdated`, `itemsUpdated`, `rowUpdated`, `columnUpdated`)
-   Child components now use native Livewire 3 reactivity for parent-child synchronization
-   Removed deprecated `protected $listeners` in favor of `#[On()]` attributes
-   Added 6 new out-of-the-box elements:
-   Hero/Banner (full-width background + overlay text + CTA)
-   Grid/Cards (services/features grid with icons/images)
-   Bullet List/Features (icon-based feature list)
-   Testimonials/Trust Badges (logos, badges, stats)
-   Accordion/FAQ (collapsible Q&A sections)
-   Call-to-Action Section (centered CTA block)
-   Added pagination to PageIndex component (15 items per page)
-   Updated element generator stub for Livewire 3 patterns
-   Cleaned up unused `target`/`source` properties from component chain
-   Performance improvements: ~70% fewer WebSocket messages for content updates

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
