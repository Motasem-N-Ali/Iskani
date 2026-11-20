<?php

/*
| PLACEHOLDER area list for the landing page (city, then village).
| It exists only so the landing screen can be built before the `areas` table (docs/ERD.md) does.
| When that table is migrated and seeded, the landing page reads from it and this file is deleted.
| The names and which village sits under which city are NOT confirmed yet. Check them first.
*/

return [
    'cities' => [
        'ramallah' => [
            'name' => ['ar' => 'رام الله', 'en' => 'Ramallah'],
            'villages' => [
                'birzeit' => ['ar' => 'بيرزيت', 'en' => 'Birzeit'],
                'surda' => ['ar' => 'سردا', 'en' => 'Surda'],
                'ein-arik' => ['ar' => 'عين عريك', 'en' => 'Ein Arik'],
                'deir-dibwan' => ['ar' => 'دير دبوان', 'en' => 'Deir Dibwan'],
            ],
        ],
        'al-bireh' => [
            'name' => ['ar' => 'البيرة', 'en' => 'Al-Bireh'],
            'villages' => [
                'beitunia' => ['ar' => 'بيتونيا', 'en' => 'Beitunia'],
                'kafr-aqab' => ['ar' => 'كفر عقب', 'en' => 'Kafr Aqab'],
                'ein-munjid' => ['ar' => 'عين منجد', 'en' => 'Ein Munjid'],
            ],
        ],
    ],
];
