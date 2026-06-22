# Release v1.0.3

Small quality-of-life update: the default Quill toolbar in `config/pagecomposer.php` has been expanded to cover common formatting needs out of the box.

## What Changed

The default value of `quill_toolbar` is now:

```php
'quill_toolbar' => [
    [['header' => [false, 1, 2, 3]]],
    ['bold', 'italic', 'underline'],
    [['list' => 'ordered'], ['list' => 'bullet']],
    ['link'],
    ['clean'],
],
```

That gives you:

- Normal / H1–H3 dropdown
- Bold, italic, underline
- Ordered and bullet lists
- Link
- Clear-formatting button

## Upgrade Notes

- **If you haven't published `config/pagecomposer.php`** — nothing to do, you'll pick up the new default automatically.
- **If you have published the config** — your custom `quill_toolbar` array (if any) stays exactly as you left it. To adopt the new default, copy the snippet above into your published config.

No breaking changes. No code changes required in consuming apps.
