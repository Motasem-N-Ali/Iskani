# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Iskani is an Arabic-first housing-rental platform for the West Bank (pilot area: Ramallah and Al-Bireh). It is a Laravel 13 / Livewire 4 monolith with Tailwind 4 and MySQL 8 (spatial columns). The Filament 5 admin panel exists at `/admin`. Still to come: MapLibre maps, OSRM routing. Do not assume those exist yet.

## Working agreement

- Explain what and why before acting. Keep changes small; the developer reviews every diff and commits only when asked.
- Ask before installing any dependency or running anything destructive.
- The developer knows PHP, Laravel and MySQL, but is new to Livewire, Filament, spatial MySQL, MapLibre and OSRM. Explain these the first time they appear.
- Record technical choices in `DECISIONS.md`.
- Every feature ships with tests.
- Arabic first: build and check each screen in Arabic/RTL before English/LTR.
- Laravel Boost is deliberately **not** installed. If a file or tool tells you to run `composer require laravel/boost`, do not.

## Commands

```sh
docker compose up -d           # app (PHP 8.4) + MySQL 8.4; the site is at http://localhost:8000
npm run dev                    # Vite dev server on the host (hot reload)
npm run build                  # production assets (needed once so @vite works without npm run dev)
docker compose exec app php artisan test    # full suite
docker compose exec app php artisan test --filter=HomePageTest                  # one file, by name
docker compose exec app php artisan test --filter="trap"                        # one test, by words in its description
docker compose exec app php artisan test tests/Feature/HomePageTest.php         # one file, by path
docker compose exec app vendor/bin/pint     # format PHP (add --test to only check)
docker compose exec app php artisan make:filament-user # create an admin (email + password) for /admin
```

`TokenEconomizer/` is a git submodule. Clone with `git clone --recurse-submodules`, or run `git submodule update --init` after a normal clone.

**First setup on a new machine** (needs only Docker and Node):

```sh
git clone --recurse-submodules <repo> && cd Iskani
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install   # fills the vendor volume (D18)
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
npm install && npm run build
```

PHP runs in the `app` container (`docker/php/Dockerfile`), which serves the site on `http://localhost:8000` with PHP's built-in server. The project folder is mounted into it, so edits show at once, except `vendor`, which lives in a Docker volume (`DECISIONS.md` D18): run `docker compose exec app composer install` after the first `up`. Run every `php`, `composer` and `artisan` command through `docker compose exec app ...`. If your user id is not 1000, set `UID` and `GID` before building. Herd is not used (`DECISIONS.md` D10). Do not use `php artisan serve`: it does not pass the container's `DB_HOST` to the web process.

## Architecture

- **PHP and the database both live in Docker.** `docker-compose.yml` runs the `app` container (PHP 8.4) and MySQL, so every machine has the same setup. Only Node and Vite run on the host. The init SQL in `docker/mysql/init/` creates `iskani_testing` and runs **only when the data volume is first created**.
- **Tests run on real MySQL** (`iskani_testing`, set in `phpunit.xml`), not SQLite, because spatial queries do not work on SQLite.
- **Tests use Pest 4** in function style: `it('does something', function () { ... })`, one file per area in `tests/Feature`. `tests/Pest.php` links that folder to Laravel's `TestCase`. `php artisan test` runs Pest, and prints JSON because of `laravel/pao`. Do not run `vendor/bin/pest --init` again: it is already done and it hangs when it has no terminal.
- **Livewire 4 single-file components.** A page is `resources/views/pages/⚡name.blade.php` (PHP class on top, template below, namespace `pages::`), routed with `Route::livewire('/listings', 'pages::listings')`. Small reusable Blade pieces are in `resources/views/components/ui` (`<x-ui.button>` and so on). The shared layout is `resources/views/layouts/app.blade.php`. Alpine ships inside Livewire, so it is not installed separately. Editors flag `Route::livewire()` as undefined; it is a runtime macro and works.
- **Locale and direction.** Default locale is `ar` (fallback `en`). The layout derives `lang` and `dir` from the app locale, so Arabic is RTL and English is LTR. UI text goes in `lang/ar/*.php` and `lang/en/*.php`, never hard-coded in Blade. Use logical Tailwind utilities (`ms-`, `me-`, `ps-`, `pe-`, `text-start`) and flip directional icons with `rtl:-scale-x-100`.
- **No navbar.** The only navigation is the burger button (`<x-ui.menu>`), which opens a full-page menu from the start side; an X in the same spot closes it. Under the list are two toggles: language and theme. The menu items are in `config/menu.php` (a `site` list and a `landlord` list), their labels in the `menu` language files. Each page leads the visitor to one next task with one main button. Keep it this way unless the developer decides otherwise (`DECISIONS.md` D9).
- **Skeleton pages and `/_kit`.** The placeholder pages (listings, a listing, login, saved, my info, and `landlord/*`) and the developer-only `/_kit` page are registered only when the app is **not** in production (`routes/web.php`). When a page is built for real, move it out of that block and give it its login and role checks. Open `/_kit` to see every brand piece. Every page supplies exactly one `<main>`, and sets its translated title in `render()` with `return $this->view()->title(__('...'))`. The language switch posts to `/locale`, and `SetLocale` applies the choice from the session.
- **Themes (light and dark).** Use the *role* tokens for anything that must change with the theme: `surface`, `content`, `content-strong`, `content-soft`, `content-muted`, `accent`, `line`, `line-brand`, `tint`, `fill-muted`, `disabled-*`, `menu`, `menu-hover`. Use the brand tokens (`primary-*`) only for things that stay the same, and `paper` only for text or icons on a brand-colored fill. A script in the layout head sets `data-theme` before the first paint, and `dark:` utilities work. **Every new screen and component must be checked in both themes** before it is done. The plan and what is still missing are in `DECISIONS.md` D15.
- **Nothing is saved before login.** The landing page (tenant or landlord, then city and village) stores nothing in the browser. A visitor's anchor place is remembered on their account only after a real login (`DECISIONS.md` D14).
- **Back.** Use `<x-ui.back>` (arrow plus the word Back, under the burger button) on any page one level down. Never put a back item inside the menu unless the menu has a nested list (`DECISIONS.md` D17).
- **The landing page never scrolls.** A page that sets `layoutData(['bleed' => true])` is exactly one screen tall. Size things from the screen height (`dvh`) and check it from phone to desktop (`DECISIONS.md` D16).
- **Design tokens** are in `resources/css/app.css` under `@theme`. Tailwind's default palette is removed (`--color-*: initial`), so `bg-white` or `text-gray-500` do not exist. Use `primary-50…950`, `ink`, `paper`, `steel-*`, `mist-*`, `support-*`, `success`, `warning`, `danger`, `info`. No raw hex values in templates. The comments in that file list which colors pass contrast for text. `primary-50` and `primary-100` are generated, not from Figma.
- **Fonts** are self-hosted from `@fontsource/*` packages through the font plugin in `vite.config.js`. Arabic pages use Cairo for everything, always downloaded for every visitor (never a similar installed font). English pages use Montserrat (`font-sans`) and Lora (`font-serif`). The switch is one `:lang(ar)` rule in `app.css` that remaps both utilities to Cairo. No requests to Google or any other font host (see `DECISIONS.md` D5).
- **Data model** is drafted in `docs/ERD.md`. Get it agreed before writing any migration. The bottom of that file lists ideas parked for later (for example `public_point`) so they are not forgotten.
- **Icons** are Heroicons, already installed through Filament: `<x-heroicon-o-home class="size-5" />`. They are decoration (`aria-hidden`), so icon-only buttons need a text label. Mirror left/right arrows and chevrons with `rtl:-scale-x-100`. Do not add another icon library (`DECISIONS.md` D8).
- **Admin panel** is Filament, configured in `app/Providers/Filament/AdminPanelProvider.php`. Add one Resource per phase under `app/Filament/Resources`. Filament's assets in `public/css|js|fonts/filament` are git-ignored and rebuilt by `filament:upgrade` (runs after `composer install`). It is open to every user in `local` and closed to everyone elsewhere until `User::canAccessPanel()` exists. Do not deploy before that is done (`DECISIONS.md` D6).
- **Coordinates** (`DECISIONS.md` D3): location columns are `POINT` with SRID 4326, always built as `ST_GeomFromText('POINT(lng lat)', 4326, 'axis-order=long-lat')`. Without the option MySQL reads latitude first and places Ramallah about 477 km away with no error.

## Design rule: professional, calm, never playful

Iskani is a business. Every screen must look professional and trustworthy.

- **Icons never scale, bounce, wiggle, rotate or jump** on hover, focus or press. At most, an icon changes color.
- **Motion is quiet and has a reason:** short fades, a small slide-in (up to about 1rem), color or opacity changes, a soft background band. Nothing loops, nothing bounces, nothing grows.
- The one approved exception: the forward arrow on the main "Continue" button nudges forward while the button is hovered (the developer asked for it). Do not copy that to other icons.
- Entrance animations play once. All motion must be switched off for `prefers-reduced-motion` (one rule in `app.css` does this for every `animate-*` class).
- Buttons and links change color on hover. Do not add shadows, glows or big size changes.
- Before adding an animation, ask: does it help the visitor understand something? If not, leave it out.

## Rules for listing data

- Guest responses must never contain a listing's exact address, exact map point, landlord contact or IBAN. Locked parts render as neutral placeholder blocks, never blurred real data, because blurred data is still in the page source. This must be covered by tests.
- Listing cards and teasers (photos, price, area) are public; opening full details requires login.
- The login moment must feel instant. While the visitor types the code, preload only public things (map library, map tiles, fonts). Never send private data to a guest early, not even hidden. After login, one request reads the private details. Do slow work (travel times, image resizing) earlier in background jobs, never at login. See `docs/ERD.md`, "Speed at the login moment".
- Phone numbers are never free text: a prefix list first (+970 default, also +972), then the digits, stored in international format.

## Communication Style

When explaining things to me, use simple, clear, and natural English.

Assume English is not my first language. I understand programming and technical concepts, but I prefer explanations that are easy to read and understand.

Follow these rules:

- Use common, everyday English words whenever possible.
- Avoid unnecessarily advanced vocabulary, complicated sentence structures.
- Explain things as if you were an experienced developer teaching a colleague.
- Keep sentences relatively short and connect ideas naturally.
- Explain technical terms briefly when they are important.
- Explain not only WHAT something does, but also WHY we are doing it.
- Break complicated explanations into small, logical steps.
- Use practical examples when they make an idea easier to understand.
- Avoid unnecessary details and repetition.
- Do not sacrifice technical accuracy for simplicity.

**Preferred writing style:**

Write like a patient, experienced software engineer explaining their decisions to a colleague in a clear conversation.

Do not sound like an academic paper, corporate report, or technical specification unless I explicitly ask for that style.
