# Vertex Football Agency

Multi-language website and admin panel for a football agency.

Laravel 13 · Inertia.js · Vue 3 · Tailwind CSS 4 · TipTap · Chart.js

## Setup

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

npm run dev
php artisan serve
```

Set `APP_URL` to the host you serve from — uploaded media resolves through it.

### Accounts

| Email | Password | Role |
|---|---|---|
| `admin@abfootball.test` | `password` | admin |
| `editor@abfootball.test` | `password` | editor |

Admin panel: `/admin`

## Languages

`ka` GEO (default) · `en` ENG · `de` GER · `es` ESP · `fr` FRA · `it` ITA · `ru` RUS

Configured in `config/localization.php`.

Content is stored per field as a JSON map (`{"ka": "…", "en": "…"}`) via
`spatie/laravel-translatable`. The admin renders one input per locale; the public
site resolves to a single string, falling back requested → Georgian → English →
first non-empty.

All site copy outside the content models lives in `lang/{locale}/ui.php` and is
shared on every Inertia response. `lang/{locale}/validation.php` covers the rules the public contact form
can hit; the rest falls back to English. The admin interface is English only.

Public URLs are locale-prefixed (`/ka/players/…`). `SetLocale` registers the
segment as a default route parameter, so `route()` keeps a visitor in their
language. `/` negotiates from cookie → `Accept-Language` → default.

## Theming

`data-theme="dark|light"` on `<html>` drives everything. Colours resolve through
CSS variables in `resources/css/app.css`, exposed to Tailwind via `@theme inline`
— components use `bg-surface`, `text-fg-muted`, `border-border`, never a hex.

Applied by an inline script before first paint, stored in `localStorage`, and
persisted against the account for signed-in admins.

Chart colours are separate from the UI accent (`resources/js/Support/chartTheme.js`)
— the brand gold falls outside the lightness band a categorical palette needs, so
each mode has its own three-hue set in fixed order.

## Layout

```
app/
  Http/Controllers/{Public,Admin,Auth}/
  Http/Middleware/          SetLocale, HandleInertiaRequests, EnsureUserIsAdmin
  Http/Requests/Admin/
  Models/                   + Concerns/{HasSlug,HasMedia}
  Support/
    Locales.php             locale list, fallback resolution
    MediaUploader.php       upload decoding, validation, storage
    RichText.php            allow-list sanitiser for editor HTML
    RepeaterSync.php        reconciles HasMany repeaters
    Presenters/             model → locale-resolved payload

resources/js/
  Components/{Ui,Form,Data,Viz,Site}/
  Layouts/                  PublicLayout, AdminLayout, AuthLayout
  Pages/{Public,Admin,Auth}/
```

List views use `DataTable`, translatable fields use `LanguageTabs`, images use
`ImageUploader`, repeatable blocks use `Repeater`.

## Content model

`Player` follows the six sections from the brochure — the same numbering appears
in the admin form and on the public profile:

| | Section | Fields |
|---|---|---|
| 01 | Personal data | photos, names, DOB, nationality, height, weight, position, foot, club, contract, contact |
| 02 | Profile | playing style, pitch marker, skill ratings |
| 03 | Career | club timeline, achievements |
| 04 | Statistics | per-season totals, playing-time split, goals/assists by month |
| 05 | Photos | reorderable gallery |
| 06 | Goals | short / mid / long term, quote |

Also `Trainer`, `TeamMember`, `News` (categories, scheduling, homepage feature
flag + order), `Setting`, `ContactMessage`, `ActivityLog`.

Site-wide copy — name, tagline, address, phone, email, social links — is edited
under **Settings**. There is no page-content editor: the home and about-us copy
lives in `lang/{locale}/ui.php` under `home.info_*` and `about.*`, stored as
arrays of paragraphs and read through the `list()` helper in `useI18n`.

Playing-time percentages should total 100% but are not enforced — the form shows
a running total and warns; the donut normalises whatever it gets.

## Security

- Editor HTML passes through `RichText` (allow-listed tags, attributes, URL
  schemes; `style` limited to `text-align`).
- Uploads are checked by magic bytes against the claimed mime type. SVGs
  containing scripting are rejected.
- Contact form: per-IP rate limit + honeypot.
- Login throttled per email+IP and per route.
- Account management is admin only; the last admin cannot be removed or demoted.

## Tests

```bash
php artisan test
vendor/bin/pint
```

Covers every public page in all seven locales, translation fallback,
draft/scheduled visibility, locale negotiation, filtering, admin CRUD, repeater
reconciliation, rich-text sanitisation, role enforcement, theme persistence,
contact storage, honeypot, and localised validation messages.
