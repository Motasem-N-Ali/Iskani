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

    public function test_home_page_loads_no_fonts_from_other_websites(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringNotContainsString('fonts.gstatic.com', $html);
        $this->assertStringNotContainsString('fonts.bunny.net', $html);
    }

    public function test_arabic_page_uses_only_cairo(): void
    {
        $this->get('/')
            ->assertSee('font-family: "Cairo"', false)
            ->assertSee('rel="preload" as="font"', false)
            ->assertDontSee('Montserrat', false)
            ->assertDontSee('Lora', false);
    }

    public function test_english_page_uses_montserrat_and_lora_but_not_cairo(): void
    {
        app()->setLocale('en');

        $this->get('/')
            ->assertSee('font-family: "Montserrat"', false)
            ->assertSee('font-family: "Lora"', false)
            ->assertDontSee('Cairo', false);
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
