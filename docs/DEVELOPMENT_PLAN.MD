# Iskani: Development Plan (local-first)
 
Status: v5, updated 2026-11-20 after the fourth round of decisions. Scope: everything up to a solid, locally running platform that is ready to be deployed. Deployment is outlined only at the end.
 
## 0. Decisions made (2026-11-20)
 
| # | Decision |
|---|---|
| 1 | **Livewire monolith** (Laravel + Blade + Livewire + Alpine + Tailwind). No separate SPA/API for now. |
| 2 | **Arabic first, English second.** RTL is the default direction; LTR is the variant. |
| 3 | **Location data follows the government registry** (governorate, then cities and towns/villages registered under it). The **visitor experience is simpler: City first, then Town/Village** (section 4). The governorate is not shown to visitors at launch because there is one launch governorate (Ramallah and Al-Bireh). |
| 4 | **No admin approval of listings.** Landlords publish directly. Safety comes from phone verification, reports, rate limits and fast admin takedown. |
| 5 | **Brand and visual identity exist in Figma** (file "Iskani Website Design"). It is design inspiration built from the brand principles of blue, black and white, not a full design. The build keeps its **font and colors** (section 5) and improves on the rest. |
| 6 | **A full admin panel to monitor everything** on the website. Built with Filament, growing a little in every phase. |
| 7 | **Working style:** this chat is for talking, planning and deciding. Coding happens in Claude Code in the terminal with the developer reviewing every change. We build step by step and learn together. |
| 8 | **Kafr Aqab** appears in the town/village list for the launch area. Its official parent in the registry stays the Jerusalem governorate in the data, so it can show under both governorates later when the governorate step returns. |
| 9 | **Product principle: Iskani serves people in a hurry and hungry to find a home, and nobody should feel overwhelmed.** Be basic and simple: one question at a time, then straight to listings. |
| 10 | **The town/village choice decides the results:** choosing Birzeit shows all housing in Birzeit, Surda shows Surda, Abu Qash shows Abu Qash, and so on. |
| 11 | **Listing cards are public; opening a listing asks for login.** First catch the visitor's attention and curiosity with the card and a teaser, so they truly want to log in (section 4). |
| 12 | **Area names are chosen by Claude for the seed data and editable later** (section 4). UI text lives in Laravel translation files; area names live in the database and are editable in the admin panel. |
 
## 1. Principles
 
1. **Hasty and simple first.** Fewest possible steps and choices between arriving and seeing listings. No sign-up wall before browsing.
2. **Solid, not minimal.** Fewer features, each one finished: validated, secured, tested, Arabic/RTL-ready, good on a phone.
3. **Different by design.** Iskani guides the tenant instead of dropping them into a list. The guided flow is the product identity.
4. **Local first.** Everything runs on the developer machine via Docker, including map and routing services.
5. **Vertical slices.** Each phase ends with something a person can click through.
6. **Two experiences** (Core Business Description): landlord and tenant have different onboarding, layouts, navigation and route groups.
7. **Palestine-first:** Arabic, NIS (ILS), +970 phones, West Bank geography and travel realities.
8. **Understand everything we build.** The developer reviews each change; new tools are explained when first used.
9. **Honest persuasion.** Curiosity is earned by showing real value, never by tricks or fake urgency.
 
## 2. Stack
 
| Layer | Choice | Why |
|---|---|---|
| Backend | Laravel 13 (PHP 8.3+) | Known to the developer |
| Frontend | Blade + Livewire + Alpine.js + Tailwind CSS, RTL-first, **Cairo** font, brand colors from Figma | SEO-friendly server-rendered listings, fast to build |
| Admin panel | Filament 5 (works with Laravel 13 and Livewire 4) | Tables, filters, forms, dashboards and permissions inside the same app. Verify Arabic/RTL in phase 0 |
| Database | MySQL 8 with spatial columns (POINT, SRID 4326) | Known to the developer |
| Maps | MapLibre GL JS + OpenStreetMap-based tiles | No vendor lock-in |
| Routing | OSRM or Valhalla in Docker, regional OSM extract, walking + driving profiles | Minutes and distance per landmark, free locally |
| Landmarks | Imported from OSM (Overpass) into our own `landmarks` table, curated by hand for the pilot | Fast queries, no per-request cost |
| Storage | Local disk now, S3-compatible later; images resized to WebP, EXIF stripped | EXIF can leak a home's exact GPS position |
| Queue / cache | Database queue first, Redis later if needed | Small local setup |
| Auth | Phone + password with OTP verification; email optional | Phone is the main identity in Palestine |
| Tests | Pest (or PHPUnit) + a few browser tests | Keeps changes safe |
| Dev environment | Docker Compose for MySQL and routing; PHP via Herd or Docker | Reproducible |
 
## 3. Domain model (first pass)
 
- `users` (role: landlord | tenant | admin; phone, verified_at, locale, suspended_at)
- `landlord_profiles` (display name, default contact info, IBAN, bank name, bio)
- `tenant_profiles` (situation: student | employee | family | other; anchor points; budget; preferences; last chosen city and town)
- `areas` (level: governorate | city | town; name_ar, name_en, slug, official_parent_id, optional centre point, sort_order, is_active). The official parent follows the government registry.
- `housings` (landlord, type: apartment | room [extensible], title, description, area_id (a city or a town/village), address_text (landlord-written specific address), exact_point, public_point, floor_number, building_floors, rent_amount, currency, status: draft | available | not_available | suspended, notes, contact fields)
- `housing_photos`, `amenities`, `housing_amenities` (included / not included in the monthly bill: electricity, water, internet, building services such as cleaning; plus furnished, parking, elevator...)
- `rent_changes` (audit: who, when, old, new)
- `landmarks`, `housing_landmarks` (precomputed walking/driving minutes and distance)
- `saved_housings` (the Figma sidebar has "Saved")
- `follows`, `notifications`
- `reports`, `audit_logs`
 
An `area_placements` table (show an area under extra parents) is not needed at launch and is deliberately left out. Add it only if the governorate step comes back and Kafr Aqab must show under two governorates.
 
## 4. Landing, location and the login moment
 
Designed for hurry and simplicity: one question per screen, then straight to results.
 
### 4.1 Flow
 
1. **Landing:** a single focused screen, "Choose where to live". One **City** dropdown: Ramallah, Al-Bireh, Beitunia.
2. **Fade / swipe** to the next screen: one **Town / Village** dropdown: Birzeit, Surda, Abu Qash, Jifna, Dura al-Qare', Kafr Aqab.
3. **Loading screen:** a loading ring in the middle, only while the listings are really loading.
4. **Listings** directly, with no sign-up wall. The cards are visible to guests.
5. **Pressing a listing asks for login** (section 4.3).
 
### 4.2 What the choices show
 
- **Town/village chosen:** all housing options in that town or village only. Birzeit shows Birzeit, Surda shows Surda, Abu Qash shows Abu Qash.
- **City only:** a first option "All of [city]" in the town list (proposal, to be confirmed) shows the housing registered in that city itself.
- Because the registry puts towns and villages under the governorate and not inside a city, the town list is the same whichever city was chosen. The city choice only changes the results when "All of [city]" is chosen.
- Return visitors: remember the last city and town, and offer a one-tap "Continue with Ramallah / Birzeit". They can always change it.
- In Arabic (RTL) the swipe moves in the reading direction (next screen enters from the left). Respect `prefers-reduced-motion` by using a simple fade.
- No artificial waiting. If listings load in 300 ms, show them in 300 ms; use a short minimum display time only to avoid a flash.
- Adding a city or town is a data change in the admin panel, not a code change.
 
### 4.3 The login moment: catch attention first, then ask
 
The visitor has already seen real, appealing things on the card (photos, price per month, area, what the bill includes, nearest landmark). When they press it:
 
1. The listing opens as a **teaser**: the photos and the main facts stay visible.
2. A **login sheet slides over it** (not a separate page), with a clear Arabic promise of what they unlock: full address, map with landmarks and travel times, owner contact, rent payment details, and the option to save and follow.
3. Behind the sheet the locked parts show as **neutral placeholder blocks**, not blurred real data. Blurred real data can still be read from the page source, so the guest response must never contain the address, exact map point, contact or IBAN (this is a tested rule).
4. **Login must be fast:** phone number and a code, a few seconds. After login the visitor returns to the same listing, with the address revealed.
5. Keep it honest: no fake countdowns, no invented "5 people are viewing". Curiosity comes from real value.
6. Trade-off to note: pages behind login are not indexed by search engines with their details. The teaser part (photos, price, area) stays public and indexable, and the locked parts stay hidden.
 
### 4.4 Area names
 
Area names are written by Claude for the seed data, and anyone can edit them later in the admin panel. Interface text (labels, buttons, messages) lives in Laravel's normal translation files (`lang/ar`, `lang/en`), also editable by hand.
 
| Level | Arabic | English |
|---|---|---|
| Governorate | رام الله والبيرة | Ramallah and Al-Bireh |
| Governorate (data only, for Kafr Aqab) | القدس | Jerusalem |
| City | رام الله | Ramallah |
| City | البيرة | Al-Bireh |
| City | بيتونيا | Beitunia |
| Town/village | بيرزيت | Birzeit |
| Town/village | سردا | Surda |
| Town/village | أبو قش | Abu Qash |
| Town/village | جفنا | Jifna |
| Town/village | دورا القرع | Dura al-Qare' |
| Town/village | كفر عقب | Kafr Aqab |
 
Spellings follow common usage and should be compared with the government registry when the developer has it at hand.
 
**Kafr Aqab:** its official parent is the Jerusalem governorate. Because the barrier separates it from the rest of Jerusalem, travel-time estimates from there (especially toward Jerusalem destinations) are the least reliable in the platform and must stay labelled as estimates. Its landmarks are curated by hand in phase 4.
 
## 5. Design: font, colors and tips (from the Figma file)
 
The Figma file is inspiration, not a finished design. Frames reviewed: the landing screen ("Choose where to live"), the listings screen with the user sidebar, the language-switch overlay and the palette sheet. The mobile frames were empty when read, so mobile layouts are still to be designed.
 
**Font:** **Cairo** (Bold 64 px for the page headline, SemiBold 24 px for field labels in the landing design). Cairo covers Arabic and Latin, so English uses the same family. Self-host it with only the Arabic and Latin subsets, and use `font-display: swap`, because the first screen must render fast on a slow network. Use responsive sizing (for example `clamp(2rem, 6vw, 4rem)` for the headline) so the 64 px heading does not crowd a phone.
 
**Colors** (exact values from the palette sheet):
 
| Token | Hex | Notes |
|---|---|---|
| blue-950 | #040b26 | |
| blue-900 | #091954 | |
| blue-800 | #0e2782 | |
| blue-700 | #1335b0 | |
| **blue-600 (brand)** | **#1843de** | main brand blue, buttons, ring, logo |
| blue-500 | #3f64ea | |
| blue-400 | #6d89ef | |
| blue-300 | #9baef4 | |
| blue-200 | #c9d3f9 | |
| cyan (support) | #18a6de | |
| violet (support) | #5018de | |
| slate-800 / 600 / 400 | #2e4260 / #496288 / #7e90a9 | neutrals |
| gray-100 / 50 | #eeeff1 / #fafafa | |
| white | #fdfdfd | page background |
| black | #171717 | text |
| accents | green #00cc44, orange #ff6b00, red #e53e3e, blue #0066ff | status colors |
 
The 9-step blue scale becomes the Tailwind `primary` color; the neutrals, support and accent colors become named tokens. No raw hex values in templates.
 
**Contrast checks** (against the #fdfdfd background, WCAG AA needs 4.5:1 for normal text, 3:1 for large text and UI):
- Pass for text: #171717 (17.6), #2e4260 (10.0), #496288 (6.1), #1843de (7.1), #3f64ea (4.9). White text on #1843de is 7.1 and on #0e2782 is 12.7.
- Do not use for normal text: #7e90a9 (3.2), #6d89ef (3.2), #8e8e8e (3.2), #bcbcbc (1.9), red #e53e3e (4.1, only for large text), orange #ff6b00 (2.8), green #00cc44 (2.1), cyan #18a6de (2.7). Use these as fills, icons or borders, or put dark text on them.
- In the listings mock, the small gray location text on the card looks too light. Use #496288 or darker for small text.
 
**Tips to improve on the inspiration:**
1. **Make the dropdown the hero.** Fields about 56 px tall on phones, label above, large tap target, the blue outlined style from the design. With only a handful of options, a custom list that opens in place beats a tiny native control, but keep it keyboard and screen-reader accessible.
2. **Decorations must be cheap.** The big blue rings and the skyline are nice brand touches. Use optimized SVG, mark them `aria-hidden`, shrink or hide the rings on phones, and never let them push the dropdown below the fold.
3. **Turn the loading ring into a brand moment.** A ring in #1843de on a #c9d3f9 track echoes the logo's ring. Show a clear Arabic message under it. Use skeleton cards on later page loads instead of a full-screen ring so repeat visits feel instant.
4. **Listing cards carry the Iskani difference and the curiosity.** The Figma card has photo carousel, save icon, price per month in NIS and area. Add, in this order of importance: what the monthly bill includes (small icons) and the nearest landmark with travel time ("8 min walk to the university"). These are what make the visitor want to open it. Keep the card calm, no more than 4 data points.
5. **The login sheet is part of the design, not an interruption.** Brand it like the rest of the site, a short headline about what they unlock, a phone field, a big button. See section 4.3.
6. **Show the price clearly:** "1200 ₪ / month" with the shekel sign in the right reading position for RTL, and a consistent number format.
7. **Use logical CSS properties** (`ms-`, `me-`, `ps-`, `pe-`, `start`, `end`) everywhere so RTL and LTR both work. Mirror directional icons (arrows, chevrons, back).
8. **Navigation:** the design's right-hand sidebar (My info, Saved, Log out) works on desktop. On phones use a bottom bar or a slide-in drawer, with the bell and menu icons from the landing screen kept at the top.
9. **Language switch** is a nice full-screen blue overlay in the design. Keep it, and remember the choice and set `lang` and `dir` on the page.
10. **Arabic typography:** Cairo has tall ascenders and diacritics, so use a line-height around 1.6 for body text, and avoid letter-spacing tricks that break Arabic joining.
11. **Imagery:** real photos only on listings; the placeholder grey cards in the design become skeleton loaders.
12. **The Iskani icon set frame** in Figma should be exported as one SVG sprite and used instead of an icon library, so the icons match the brand.
 
## 6. Rules from the existing docs, turned into requirements
 
- Registration is required (System Enforcement Stories). Browsing the listing cards does not require it; opening a listing does (decision 11).
- Landlord must pick the housing type: apartment or room.
- Exact address and landlord contact hidden until the tenant logs in. Guests never receive them in any response.
- Rent update: direct button, warning, audit log entry.
- Available / Not available toggle.
- "Use same contact info" button, editable form.
- Landlord can announce a new available housing to followers.
- Tenant sees (after login): monthly bill inclusions, nearby landmarks with travel times (walking/driving, minutes/hours, metres/kilometres), floor, real photos, map with landmarks, specific address, landlord contact, landlord IBAN, follow button.
- Filters: location (section 4), type.
 
## 7. Gaps found in the docs and how the plan handles them
 
1. **Recommendation Algorithm is marked NOT READY.** It defines marks (5, 2.5, 1, 0.5) and "Housing Full Frenzee" (pictures uploaded, contact info added), which reads as a listing-completeness score. Plan: a transparent rule-based version in phase 5 with weights in config.
2. **IBAN visibility** undefined. Proposal: logged-in tenants only, with a "pay only after viewing and agreeing" notice. Iskani does not process payments in this version.
3. **Housing types**: description lists apartments, rooms, houses, suites; the enforcement story allows apartment or room. Plan: apartment and room now, extensible enum.
4. **Landlord Story duplicates** and "number of floors" vs "floor number": treated as unit floor + building total floors.
5. **Follow notifications**: in-app + email first; SMS/WhatsApp once a provider exists.
6. **Currency**: NIS default; ILS, JOD, USD supported in the model.
7. **Travel time honesty**: OSM routing does not know checkpoints or closures. Show times as estimates.
8. **No listing approval (decision 4)** means a scam listing is live immediately. Mitigations in phase 7: verified phone required, report button, rate limits for new landlords, duplicate phone/photo detection, one-click takedown and suspension in the admin panel, and an audit trail.
9. **Admin panel was missing from the stories** and is now decision 6.
10. **Saved listings and a "My info" page** appear in the Figma sidebar but not in the stories. Added as small items in phase 3.
 
## 8. How we work (Claude Code + this chat)
 
- **This chat:** talk, decide, plan, review designs, update these project docs.
- **Claude Code in the terminal:** build, with the developer reviewing every diff.
- **Slice loop:** explain what and why, plan the files, implement a small slice, developer reviews, run tests, commit, short note of what was learned.
- **`CLAUDE.md` rules (to write in phase 0):** explain before changing; small diffs; never send the address, exact point, contact or IBAN to guests; Arabic/RTL first; use design tokens, never raw hex; UI text in `lang/` files; tests with every feature; ask before adding a dependency; record choices in `DECISIONS.md`.
- **New to the developer (explained when first used):** Livewire, spatial MySQL, MapLibre, OSRM/Valhalla and OSM data, Filament, queues and jobs, RTL styling, image processing.
- **Figma:** the Figma connector can read the file from here. Screens are checked against it when built. Because Figma asset links expire and the sandbox cannot download them, logo, skyline and icons are exported from Figma by the developer once into the repo as SVG.
 
## 9. Open questions
 
1. **"All of [city]" option:** confirm that the first option in the town list shows housing registered in the city itself (section 4.2).
2. **Mobile designs:** the iPhone frames in Figma were empty. Design them before phase 3.
3. **Admin roles:** one super admin at first, or also a support/moderator role?
4. **Recommendation algorithm:** finish the doc or approve the rule-based version.
5. **Area name spellings:** compare the seed names in section 4.4 with the government registry.
 
## 10. Phases
 
Each phase ends with a "done when" check. The admin panel grows with every phase: each phase adds its own Filament resource.
 
### Phase 0: Foundations
- Repo, Docker Compose, Laravel 13, Tailwind with RTL, Arabic + English translation files, MySQL spatial migration test.
- Figma tokens into Tailwind (section 5): Cairo font self-hosted, the blue scale, neutrals, accents; base Blade components (button, input, dropdown, card, bottom sheet, layout shell); logo, skyline and icons exported as SVG.
- Filament admin panel installed at `/admin`, confirm Arabic/RTL support.
- `CLAUDE.md`, `DECISIONS.md`, ERD agreed before migrations.
- **Done when:** a fresh clone runs with one command, a branded Arabic page and an admin login work, tests are green.
 
### Phase 1: Accounts, roles, two setups
- Register, login, logout, phone OTP (locally logged to a file), password reset. The login must also work as a sheet that returns the visitor to what they were doing.
- Role chosen at registration; separate onboarding and dashboards for landlord and tenant.
- Policies and middleware; landlord profile and tenant profile.
- Admin: users resource (view, search, suspend).
- **Done when:** both roles register, verify, log in, land on different dashboards; authorisation tests pass.
 
### Phase 2: Areas and the landlord side
- Seed the area tree with the names in section 4.4; admin can add and edit areas and names.
- Housing create / view / edit / delete as a step-by-step form: type, city or town/village, address text, map pin, floor, bill inclusions, rent, notes, contact with "use my contact info" button.
- Photo upload: resize, WebP, EXIF stripping, ordering, cover.
- My housings list with location filter and quick Available / Not available toggle.
- Rent update with warning and audit trail; completeness meter.
- Admin: housings, areas, amenities resources.
- **Done when:** a landlord publishes a complete listing from a phone-sized screen, and every edit and rent change is logged.
 
### Phase 3: Landing flow, browse and the login moment
- The landing flow from section 4.1: city dropdown, swipe/fade to town dropdown, loading ring, listings. Remember last choice.
- Results by town or village (section 4.2); listing cards with photos, price, area, bill inclusions.
- Listing teaser plus login sheet (section 4.3); after login the same listing opens with the address, contact, IBAN, map and landmarks unlocked.
- Saved listings, "My info", language switch overlay.
- Guided onboarding wizard starts: "I'm a student / employee / family", anchors, budget (after the first listings, never before).
- **Done when:** from the landing page to listings takes two choices and no sign-up; pressing a listing as a guest shows the teaser and login sheet; tests prove the address, exact point, contact and IBAN never appear in any guest response or page source.
 
### Phase 4: Geo intelligence
- Local routing service; landmark import and manual curation for the pilot area, including Kafr Aqab.
- Queued job computes nearest landmarks and travel times per housing.
- Detail page shows a landmark map and a readable list, labelled as estimates; the card shows the nearest landmark.
- Admin: landmarks resource and a "geo data health" view.
- **Done when:** a seeded listing shows correct nearby landmarks and times, and recomputes when the pin moves.
 
### Phase 5: Guidance and recommendations
- "Best for you" results from the tenant's anchors, commute, family needs, budget and listing completeness, each with a clear "why".
- Weights in config; algorithm doc applied once finished.
- **Done when:** student, employee and family personas get visibly different top results from the same data, with explanations.
 
### Phase 6: Follow and announce
- Follow / unfollow, announce action, in-app notifications and email, mute, rate limit.
- Admin: notifications log.
- **Done when:** an announcement reaches only current followers, once, and can be stopped by them.
 
### Phase 7: Admin monitoring and safety
- Admin dashboard: counts and trends (users by role, listings by area/status, new this week, reports open), recent activity, failed jobs.
- Reports queue, takedown and suspend actions, audit-log viewer, role-based admin permissions.
- Safety rules from section 7 point 8.
- **Done when:** the admin sees the state of the whole platform on one screen, takes down a scam listing and suspends a user, each with an audit record.
 
### Phase 8: Hardening
- Tests for every rule in section 6; browser tests for main journeys (especially the landing flow and login sheet in Arabic and English); N+1 and index review; image, font and map performance; accessibility (contrast from section 5, keyboard, reduced motion); Arabic proofreading and RTL visual checks; security review; backup script; realistic seed data for the pilot area.
- **Done when:** the suite is green, the security checklist is signed off, and it feels fast on a mid-range phone with a slow network.
 
### Phase 9: Go-live preparation (outline)
- Hosting and domain, backups, object storage, HTTPS, email and SMS providers, error monitoring, uptime check, Arabic privacy policy and terms, landlord onboarding plan for the pilot area.
 
## 11. Out of scope for now
 
Online payments and escrow, in-app chat, contracts and e-signing, reviews and ratings, native mobile apps, house/suite types, checkpoint-aware routing, machine-learned recommendations, the governorate step in the visitor flow. The structure leaves room for all of them.# Iskani: Development Plan (local-first)
