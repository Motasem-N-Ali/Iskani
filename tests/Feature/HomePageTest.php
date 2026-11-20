<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_home_page_is_arabic_and_rtl_by_default(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false)
            ->assertSee('اختر أين تريد أن تسكن');
    }

    public function test_home_page_is_english_and_ltr_when_locale_is_english(): void
    {
        app()->setLocale('en');

        $this->get('/')
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('dir="ltr"', false)
            ->assertSee('Choose where to live');
    }
}
