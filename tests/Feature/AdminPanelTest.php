<?php

it('sends a guest from /admin to the login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('shows the admin login page in Arabic, right to left', function () {
    $this->get('/admin/login')
        ->assertOk()
        ->assertSee('lang="ar"', false)
        ->assertSee('dir="rtl"', false);
});

it('loads no fonts from other websites on the admin login page', function () {
    $html = $this->get('/admin/login')->assertOk()->getContent();

    expect($html)
        ->not->toContain('fonts.googleapis.com')
        ->not->toContain('fonts.gstatic.com')
        ->not->toContain('fonts.bunny.net');
});
