<?php

/*
| The one place that defines the side menu (the burger menu). There is no navbar.
| Each item: `key` (its label is in the menu language files, under `items`), a Heroicon name, and a route name.
| Items whose route does not exist (for example skeleton pages in production) are skipped.
| `landlord.*` pages show the `landlord` list, every other page shows `site`.
*/

return [
    'site' => [
        ['key' => 'home', 'icon' => 'heroicon-o-home', 'route' => 'home'],
        ['key' => 'saved', 'icon' => 'heroicon-o-bookmark', 'route' => 'saved'],
        ['key' => 'me', 'icon' => 'heroicon-o-user', 'route' => 'me'],
        ['key' => 'landlord', 'icon' => 'heroicon-o-key', 'route' => 'landlord.dashboard'],
        ['key' => 'login', 'icon' => 'heroicon-o-arrow-right-on-rectangle', 'route' => 'login'],
    ],

    'landlord' => [
        ['key' => 'dashboard', 'icon' => 'heroicon-o-squares-2x2', 'route' => 'landlord.dashboard'],
        ['key' => 'housings', 'icon' => 'heroicon-o-building-office-2', 'route' => 'landlord.housings'],
        ['key' => 'create', 'icon' => 'heroicon-o-plus-circle', 'route' => 'landlord.housings.create'],
        ['key' => 'site', 'icon' => 'heroicon-o-home', 'route' => 'home'],
    ],
];
