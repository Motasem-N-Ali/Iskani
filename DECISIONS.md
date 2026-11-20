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
- **Known gap, the shekel sign ₪:** it is not in our self-hosted font subsets (Cairo, Montserrat and Lora). The browser borrows it from a system font, and it shows correctly in Chrome on Windows, but that is not guaranteed on every device. Decide a firm solution when we build price display (Phase 2 or 3): either a tiny font subset that includes U+20AA, or an inline SVG sign. Test it on a phone.

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

## D9. Navigation: no navbar, one burger menu (2026-11-20)

- **No navbar, no bottom bar, no side bar.** The screen stays almost empty so people stay on one task. The only navigation is a burger button in the top corner (the start side: right in Arabic). It opens a full-page menu that slides in from that side, and an X in the same spot closes it. Escape also closes it, and focus stays inside while it is open.
- The menu holds a few links and the language switch. Its items are defined in `config/menu.php` and shown by one component (`<x-ui.menu>`), so wrapping it into something smarter later means changing one place.
- Pages lead the visitor to the next step with one main button, so the whole skeleton can be clicked through without opening the menu.
- **Skeleton pages** are placeholders with a "Placeholder · Phase N" tag. They are never registered in production, so none of them can be deployed by accident.
- **Language switch:** the choice is kept in the session and applied by `SetLocale`. The URL does not change with the language, so one address serves both languages. **Decide before launch** whether we need separate addresses per language (for example `/en/...`) so search engines can index both. Until then, this is the simple choice.
- The 404 page always shows the default language (Arabic), because Laravel does not run the language middleware when no route matches.
- Fields: the select is a styled native `<select>` for now, which is accessible by default. The plan's custom dropdown can replace it when the landing screen is built. The phone field shows the prefix list first (+970, +972), strips non-digits and a leading 0, and sends the full international number.

## D10. PHP runs in Docker, not Herd (2026-11-20)

- **Why:** Herd does not exist for Linux, and the developer switches between a laptop and a PC, both on Ubuntu. Running PHP in Docker gives both machines the same PHP version and extensions. A new machine needs only Docker and Node. This replaces the earlier plan of PHP on the host through Herd (`iskani.test`).
- **How:** an `app` service in `docker-compose.yml`, built from `docker/php/Dockerfile` (PHP 8.4, Composer, `pdo_mysql`, `intl`, `gd`, `bcmath`, `zip`, `exif`, `pcntl`, `opcache`). The project folder is mounted in. The container runs as UID/GID 1000 (changeable) so files it writes are not owned by root. The site is at `http://localhost:8000`.
- **Server:** `php -S` with Laravel's `server.php`, with 4 workers. Not `artisan serve`: it filters the environment, so `DB_HOST=mysql` from Compose never reaches the web process and the site fails with a 500. The built-in server is for development only. Production needs a real server (nginx with PHP-FPM, or FrankenPHP).
- **Database host:** `.env` keeps `DB_HOST=127.0.0.1`. Compose overrides it to `mysql` inside the container, so the same `.env` works on every machine.
- **Vite** still runs on the host (`npm run dev`), and the browser reaches it at `localhost:5173`.


## D11. Landing page: dropdowns first, city then village (2026-11-20)

- **No "Start" button.** The visitor lands on a city dropdown and one Continue button. Step 2 shows a village dropdown, whose first option is "All of <city>", then "Show homes", which goes to `/listings?city=...&village=...`. The page is one Livewire component that holds the step and the choices.
- **Back arrow (step 2 only):** a curved arrow (`arrow-uturn-left`, mirrored in Arabic) in the same style as the burger button, directly under it. It has an `aria-label` ("Back" / "رجوع") because it is icon-only. On step 1 only the burger shows.
- **Look:** the primary logo at the top center, the rings half outside the screen (a few overlap a little), and `landing.svg` on the bottom edge. On wide screens the skyline is capped at `max-w-4xl` so it does not grow into the form. The page skips the layout's padded container through `layoutData(['bleed' => true])`. The open menu always shows the light logo at the bottom center.
- **Placeholder data:** the lists come from `config/areas.php`, because the `areas` table does not exist yet. The names and which village sits under which city are **not confirmed**. When the table is migrated and seeded, the page reads from it and the config file is deleted.

## D12. Design rule: professional and calm (2026-11-20)

- Iskani is a business, so the interface must look professional. Icons never scale, bounce, rotate or jump on hover or press. Hover changes color only (for example the back arrow turns primary blue). Motion is short and quiet: fades, a small slide-in, a soft background band. Nothing loops except the one approved forward-arrow nudge on the main Continue button.
- Applied now: the burger, the X, the back arrow and the menu icons no longer grow on hover. The back arrow is smaller (32px icon in a 48px target) and lines up with the burger's center.
- The full rule is in `CLAUDE.md`, under "Design rule".


## D13. Landing starts with "tenant or landlord" (2026-11-20)

- **Step 0 is two cards:** Tenant (a person at a laptop) and Landlord (an apartment building). Each is a button. On hover or keyboard focus the card turns primary blue and its text turns white. The illustrations are `public/images/landing/tenant.svg` and `landlord.svg` (from unDraw, free to use and change). Their purple `#6c63ff` was changed to brand primary-600 `#1843de`, and the pink `#ff6584` to primary-300 `#9baef4`. Each sits in a light panel (`primary-50`) inside the card, so its colors stay readable on the blue hover.
- **Tenant** goes on to the city dropdown (step 1), then the village dropdown (step 2), as in D11. The back arrow shows from step 1 and goes back one step at a time.
- **Landlord** goes straight to `landlord.dashboard` for now. When login exists, that page asks for it.
- **The menu lost "For landlords" and "Log in"**, because the first screen now decides the role. The visitor menu is Home, Saved, My info and Language. The landlord menu is unchanged.
- **Rings:** two sit up in the corner on the end side, spread diagonally and cut by the screen edges. One sits in the bottom corner on the start side. All use start/end, so they mirror with the language. All three are the same size so their strokes match.
- **Menu logo:** the light logo is lifted (3.5rem from the bottom).

## D14. Nothing is saved before login; anchors come after it (2026-11-20)

- **The landing page saves nothing.** Choosing tenant or landlord, or a city and village, is not stored (no cookie, no localStorage). The cards show every time `/` is opened. An earlier version saved the choice in a cookie and redirected `/` straight to it. That was removed: it also broke Back, because after choosing "landlord" the Back button returned to `/`, which sent the visitor forward again.
- **Anchors are a login feature.** When a person logs in and finishes the login steps, their place is remembered on their account, so later visits can open straight at it: the landlord area for a landlord, the listings of their city and village for a tenant. It lives in the database, as the `last_city_id` and `last_town_id` shortcut already planned in `docs/ERD.md`, and not in the browser. Nothing of this exists until login does.
- **Back is never a loop:** the landing page and every page one level down use `<x-ui.back>`.

## D17. The back control (2026-11-20)

- **One component, `<x-ui.back>`:** a curved arrow with the word "Back" (Arabic: "رجوع") under it, fixed under the burger button and lined up with its center. It never says where it goes. It is a link (`href`) for a page one level down, or a button (`wire:click`) for a step inside a page.
- **Where it shows:** the landing page from step 1 on, the listings page (back to the landing), a listing (back to the listings), and the landlord pages (dashboard back to the landing, housings back to the dashboard, add housing back to housings). Top-level menu pages (Saved, My info) have none. Pages with a back control add `pt-16` to their `<main>` so content never sits under it.
- **Not in the menu.** The menu has no back item. A back control would go inside the menu only if the menu ever had a nested list. The language and theme are toggles, so today it does not.

## D15. Light and dark themes (2026-11-20)

- **Role tokens.** The brand colors never change. Templates use *role* tokens for anything that must flip: `surface`, `content`, `content-strong`, `content-soft`, `content-muted`, `accent`, `line`, `line-brand`, `tint`, `fill-muted`, `disabled-bg`, `disabled-fg`, `menu`, `menu-hover`. Their light and dark values are CSS variables in `resources/css/app.css` (`:root` and `[data-theme='dark']`). `paper` is the fixed "text on a brand fill" color (white in both themes). Dark text pairs were chosen for WCAG AA on `surface`.
- **How the theme is chosen.** A small script in the layout head sets `data-theme` on `<html>` before the first paint: the saved choice (`localStorage` key `iskani.theme`), otherwise the system setting, and it follows the system until the visitor chooses. No flash. The menu has a Light / Dark toggle. Tailwind's `dark:` variant is tied to `data-theme='dark'`.
- **Done:** all `x-ui` components, the 404 page, the landing page (cards, back arrow, headline, logo swap: primary logo on light, light logo on dark), the menu (its own dark blue).
- **Still to do (plan):**
  1. `/_kit`: it shows raw palette swatches on purpose (they stay), but its own text and panels need role tokens, and a theme switch for checking.
  2. Every new page and component must be checked in both themes before it is called done (rule in `CLAUDE.md`).
  3. `landing.svg` has light halo circles and bright buildings. It works on dark, but a dedicated dark version would look better.
  4. The card illustrations stay on a light panel in dark mode, because they use dark outlines.
  5. Danger red on dark is only fine for large text. Lighten it if it is ever used for body text.
  6. Maps (MapLibre) will need a dark map style when they arrive.
  7. The Filament admin has its own dark mode and is not part of this.
  8. Show the saved theme choice in "My info" once that page is real.

## D16. The landing page is one screen, and the menu has two toggles (2026-11-20)

- **No scrolling on the landing page.** The page is exactly one screen tall (`h-dvh`, `overflow-hidden`, also on `<body>`) through the layout's `bleed` option. The logo, the card illustrations, the gaps and the skyline are sized from the screen height (`dvh`), so everything shrinks together on short screens, and the content block is wider (`max-w-3xl`) so the headline stays on one line. Checked from 360x640 up to 1920x1080: the page height equals the window height and the cards always end above the skyline. Very short landscape phones (under about 450px high) were not designed for.
- **Language and theme are toggles** under the menu list, not an item with a sub-list. Each is a pill with two segments, the chosen one filled. There is no more back arrow inside the menu.

