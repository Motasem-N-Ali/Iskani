<?php

/*
| The one place that defines the side menu (the burger menu). There is no navbar.
| Each item: `key` (its label is in the menu language files, under `items`), a Heroicon name, a route name, and optional `params` (route parameters or a query string).
| There is no back item in the menu. A back control belongs under the burger button (`<x-ui.back>`), and inside the menu only if it ever has a nested list.
| Items whose route does not exist (for example skeleton pages in production) are skipped.
| `landlord.*` pages show the `landlord` list, every other page shows `site`.
*/

return [
    'site' => [
        ['key' => 'home', 'icon' => 'heroicon-o-home', 'route' => 'home'],
        ['key' => 'saved', 'icon' => 'heroicon-o-bookmark', 'route' => 'saved'],
        ['key' => 'me', 'icon' => 'heroicon-o-user', 'route' => 'me'],
    ],

    'landlord' => [
        ['key' => 'dashboard', 'icon' => 'heroicon-o-squares-2x2', 'route' => 'landlord.dashboard'],
        ['key' => 'housings', 'icon' => 'heroicon-o-building-office-2', 'route' => 'landlord.housings'],
        ['key' => 'create', 'icon' => 'heroicon-o-plus-circle', 'route' => 'landlord.housings.create'],
    ],
];
