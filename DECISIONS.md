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
