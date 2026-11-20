# Iskani: Decisions

Short records of technical choices and why. Newest at the bottom. Product decisions are kept in the developer's private development plan, which is not in this repository.

## D1. Local environment: Herd + Docker (2026-11-20)

PHP, Composer and the web server run on Windows through Laravel Herd (`iskani.test`). MySQL runs in Docker (`docker compose up -d`). Routing services join the same compose file in Phase 4.
**Why:** fast file access on Windows, familiar Laravel workflow, and the database matches production.

## D2. Databases: `iskani` for development, `iskani_testing` for tests (2026-11-20)

`docker/mysql/init/01-create-testing-database.sql` creates the test database. MySQL runs it **only when the data volume is first created**. If the volume already exists, create the database by hand or reset with `docker compose down -v` (this deletes all development data).
**Why:** SQLite cannot run spatial queries, so tests must use MySQL, and they must never touch development data.

## D3. Coordinates: SRID 4326, always built with `axis-order=long-lat` (2026-11-20)

- Every location column is `POINT` with **SRID 4326** (WGS 84).
- Order in our code, JSON, GeoJSON, MapLibre and OSRM is **longitude first, then latitude**: `[lng, lat]`.
- Every point is created with the order stated explicitly:
  `ST_GeomFromText('POINT(35.2034 31.9038)', 4326, 'axis-order=long-lat')`
- Never call `ST_GeomFromText(..., 4326)` without the option. For SRID 4326, MySQL 8 then reads the text as **latitude first**.
- Read back with `ST_Longitude(p)` and `ST_Latitude(p)`, never by guessing from `ST_X` / `ST_Y`.

**Verified on MySQL 8.4.13 with Google Maps coordinates: Ramallah (lat 31.904659245339186, lng 35.2028695836448) and Birzeit (lat 31.975687366394137, lng 35.19659747097564):**

| Check | Result |
|---|---|
| Explicit `long-lat` read back | lat 31.90466, lng 35.20287 |
| Ramallah to Birzeit | 7,898 m (about 7.9 km) |
| Same text without the option (the trap) | lat 35.20287, lng 31.90466, **477 km away from Ramallah**, in the Mediterranean |
| `ST_AsGeoJSON` | `[35.2028695836448, 31.904659245339186]` (lng, lat) |

**Why:** a swapped order places a listing in the wrong country without any error. A Pest test will assert that a Ramallah point stays within a few km of Ramallah after a save and reload. Add this test with the first spatial migration.

## D4. Livewire 4 with its bundled Alpine (2026-11-20)

Front end is Blade + Livewire 4.4 + Tailwind. Alpine.js ships inside Livewire, so it is **not** installed separately. Components are single-file (PHP class on top, template below), generated with `php artisan make:livewire`. Livewire 4 serves its script from a hashed URL (`/livewire-<hash>/…`), not `/livewire/update`.

## D5. Fonts (2026-11-20)

- **Arabic pages:** Cairo for everything. It is **always downloaded for every visitor**. We do not use a similar font that happens to be installed on the visitor's device, because every visitor must see the same Cairo.
- **English pages:** Montserrat for interface text (`font-sans`: buttons, labels, cards). Lora for headlines and longer descriptive text (`font-serif`).
- On Arabic pages `font-sans` and `font-serif` both become Cairo, because Lora has no Arabic letters. This switch is one `:lang(ar)` rule in `resources/css/app.css`.
- Files are self-hosted from the `@fontsource/*` npm packages through the Vite font plugin (`vite.config.js`). The page never contacts Google, Bunny or any other font host. A test checks this.
- Cairo: weights 400, 600, 700, subsets `arabic` and `latin`. Montserrat: 400, 500, 600, 700, `latin`. Lora: 400, 600, 700, `latin`. Only Cairo 400 and 700 are preloaded.
- The scaffold's Instrument Sans is removed.

## D6. Admin panel: Filament 5 at `/admin` (2026-11-20)

- Filament 5.11 runs inside the app, panel ID `admin`, set up in `app/Providers/Filament/AdminPanelProvider.php`. Each phase adds its own Resource (users, areas, housings, …).
- Primary color is our exact blue scale (same values as `primary-*` in `app.css`).
- **Font:** Filament's default, Inter, bundled with the package and served from our own site. We chose a clear, clean font for the admin and nothing more. The admin does not use Cairo. Inter has no Arabic letters, so Arabic text in the admin uses the device's system Arabic font.
- Arabic and RTL work out of the box: the login page renders `lang="ar" dir="rtl"` with Arabic labels (tested).
- Filament copies its CSS, JS and fonts into `public/css/filament`, `public/js/filament` and `public/fonts/filament`. These are git-ignored and recreated by `php artisan filament:upgrade`, which runs after every `composer install` / `update`.
- Until Phase 1 the admin signs in with email and password (`php artisan make:filament-user`). Phone login comes with the accounts work.

> **WARNING: admin access control must exist before any deployment.**
> In the `local` environment Filament lets **every user** into `/admin`. In production it blocks **everyone** unless the `User` model implements `Filament\Models\Contracts\FilamentUser` with a `canAccessPanel()` method. We have no roles yet (Phase 1), so this is not done. Phase 1 must add the role check, with a test that a tenant or landlord gets a 403 on `/admin`, and nothing goes live before that.

## D7. Tests: Pest 4 on PHPUnit 12 (2026-11-20)

- Pest 4.7 with the Laravel plugin, in function style. The scaffold's PHPUnit class tests and its two placeholder examples were replaced.
- Pest pins PHPUnit tightly, so PHPUnit moved from 12.5.45 down to **12.5.33** (same major version). Do not raise PHPUnit by hand. Update Pest instead.
- We stay on **Pest 4**, not 5. Pest 5 needs PHPUnit 13, but our `composer.json` asks for PHPUnit `^12.5.12` (the Laravel scaffold default). Moving to Pest 5 later means moving PHPUnit to 13 on purpose, in its own step.
- `laravel/pao` (from the Laravel scaffold) prints test results as one line of JSON. Keep it, because it keeps test output short for coding agents.
- `tests/Feature/SpatialPointsTest.php` creates and drops a throwaway table `spatial_probe` in `iskani_testing`. It checks the SRID 4326 and `long-lat` rule from D3 with real Ramallah and Birzeit coordinates, including the 477 km "trap" case.

## D8. Icons: Heroicons (2026-11-20)

- **Why Heroicons:** it is the set made by the Tailwind team, it has 1,288 icons in four styles, and it is **already installed**, because Filament needs it (`blade-ui-kit/blade-heroicons`). The public site and the admin panel use the same set, and we install nothing new. This replaces the earlier idea of exporting an icon set from Figma.
- **Use in Blade:** `<x-heroicon-o-home class="size-5" />`, or `svg('heroicon-o-home', 'size-5')` in PHP. Styles: `o` outline 24 px (default choice), `s` solid 24 px, `m` mini 20 px, `c` micro 16 px.
- **Accessibility:** icons render with `aria-hidden="true"`, so they are decoration. A button that has only an icon needs a text label (`aria-label`, taken from `lang/` files).
- **RTL:** mirror icons that point left or right (arrows, chevrons, "back") with `rtl:-scale-x-100`. Do not mirror the others (home, phone, bell).
- **Stored names:** `amenities.icon` holds the full Heroicon name, for example `heroicon-o-bolt`.
- **Gaps:** if a concept has no good Heroicon (for example elevator, parking, water drop), use the nearest one. Add a custom SVG only if nothing is close.
- **Not icons:** the logo, the rings and the skyline are brand images, kept in `public/images/`.
