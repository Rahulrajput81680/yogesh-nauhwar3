# Language Toggle Functionality (Hindi <-> English)

## Why `components/translations.php` exists

`components/translations.php` is the base translation source for shared UI labels and fallback phrase mapping.

It is used for:

- Global reusable keys (`keys`) like navbar labels, contact button text, form placeholders, success/error messages.
- Phrase fallback map (`phrases`) for legacy template text that still exists in old HTML.

So this file is now the single translation source for the entire frontend, including section-heavy page content.

## Why a single file is used

`components/translations.php` stores both reusable keys and structured section-by-section content for pages (for example, `home` and `about`) with both languages.

Example structure:

- `home.hero.slide1.title.hi`
- `home.hero.slide1.title.en`
- `about.history.timeline[0].description.hi`

This keeps large page content maintainable and avoids hardcoded English text in templates.

## How toggle works end-to-end

### 1) Toggle UI

- Toggle button is in `components/header.php`.
- Clicking toggle adds `?lang=hi` or `?lang=en` to current URL.

### 2) Language state storage

In `components/frontend-init.php`:

- Reads `lang` from query string.
- Validates against supported languages (`en`, `hi`).
- Stores selected language in:
  - Session: `$_SESSION['site_lang']`
  - Cookie: `site_lang` (30 days)
- Default language is `en`.

### 3) Translation data loading

In `components/frontend-init.php`:

- Loads `components/translations.php` into an in-memory translation array.
- Uses `content` directly from `components/translations.php`.
- Derives phrase mappings (`en -> hi`) from nested content automatically.

### 4) Rendering APIs

In `components/frontend-init.php`:

- `frontend_content('path.to.node')`
  - Fetches nested content from `translations.php` by dot path.
  - Returns selected language value when node has `{en, hi}`.
- `translate('key')`
  - Fetches common key text from `translations.php`.

### 5) Page-level content rendering

- `index.php` and `about.php` were updated to fetch section text directly via `frontend_content(...)` paths.
- This removes hardcoded English for core page content.

### 6) Legacy fallback translation

- For old static text that may still exist in templates, output-buffer translation (`ob_start`) is enabled for Hindi.
- A regex-based phrase matcher replaces known English phrases with Hindi safely across whitespace/newlines.

## Current result

When user selects Hindi:

- Navbar/button/common labels switch to Hindi (from `translations.php`).
- Home/About section content switches to Hindi (from `translations.php` content block).

When user selects English:

- Same sections render English values from the same source.

## Files involved

- `components/header.php`
- `components/frontend-init.php`
- `components/translations.php`
- `index.php`
- `about.php`

## Add new translated content in future

1. Add content in `components/translations.php` under the `content` key with both `hi` and `en`.
2. Use `frontend_content('path.to.content')` in page template.
3. For shared labels/buttons/inputs, add/update `components/translations.php` and call `translate('key')`.

This keeps the bilingual system clean, scalable, and reusable.
