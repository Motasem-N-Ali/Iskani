<?php

use Tests\TestCase;

/*
| Feature tests run inside Laravel (HTTP, database, views) through Tests\TestCase.
| They use the real MySQL database `iskani_testing` (see phpunit.xml), so Docker must be up.
|
| Add `->use(RefreshDatabase::class)` here once tests need the app's tables.
*/

pest()->extend(TestCase::class)->in('Feature');
