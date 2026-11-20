<?php

it('is Arabic and right to left by default', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('lang="ar"', false)
        ->assertSee('dir="rtl"', false)
        ->assertSee('اختر أين تريد أن تسكن');
});

it('is English and left to right when the locale is English', function () {
    app()->setLocale('en');

    $this->get('/')
        ->assertOk()
        ->assertSee('lang="en"', false)
        ->assertSee('dir="ltr"', false)
        ->assertSee('Choose where to live');
});

it('loads no fonts from other websites', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->not->toContain('fonts.googleapis.com')
        ->not->toContain('fonts.gstatic.com')
        ->not->toContain('fonts.bunny.net');
});

it('uses only Cairo on the Arabic page', function () {
    $this->get('/')
        ->assertSee('font-family: "Cairo"', false)
        ->assertSee('rel="preload" as="font"', false)
        ->assertDontSee('Montserrat', false)
        ->assertDontSee('Lora', false);
});

it('uses Montserrat and Lora, and not Cairo, on the English page', function () {
    app()->setLocale('en');

    $this->get('/')
        ->assertSee('font-family: "Montserrat"', false)
        ->assertSee('font-family: "Lora"', false)
        ->assertDontSee('Cairo', false);
});
