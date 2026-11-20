<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    public function test_guest_is_sent_from_admin_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_login_page_is_arabic_and_rtl(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false);
    }

    public function test_admin_login_page_loads_no_fonts_from_other_websites(): void
    {
        $html = $this->get('/admin/login')->assertOk()->getContent();

        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringNotContainsString('fonts.gstatic.com', $html);
        $this->assertStringNotContainsString('fonts.bunny.net', $html);
    }
}
