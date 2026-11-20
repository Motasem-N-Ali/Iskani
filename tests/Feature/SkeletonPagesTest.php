<?php

use Illuminate\Support\Facades\Process;

/*
| The skeleton: every placeholder page, the burger menu, the language switch and the 404 page.
| The kit page is checked on its own because it mentions font names in its text.
*/

dataset('pages', [
    'home' => '/',
    'listings' => '/listings',
    'listing' => '/listings/1',
    'login' => '/login',
    'saved' => '/saved',
    'me' => '/me',
    'landlord dashboard' => '/landlord',
    'landlord housings' => '/landlord/housings',
    'add housing' => '/landlord/housings/create',
]);

dataset('locales', [
    'Arabic' => ['ar', 'rtl'],
    'English' => ['en', 'ltr'],
]);

it('shows every page with the right language and direction', function (string $url, string $locale, string $dir) {
    $this->withSession(['locale' => $locale])
        ->get($url)
        ->assertOk()
        ->assertSee('lang="'.$locale.'"', false)
        ->assertSee('dir="'.$dir.'"', false);
})->with('pages')->with('locales');

it('has one main area and the burger menu as the only navigation', function (string $url) {
    $html = $this->get($url)->assertOk()->getContent();

    expect(substr_count($html, '<main'))->toBe(1)
        ->and(substr_count($html, '<nav'))->toBe(1)
        ->and($html)->toContain('aria-controls="site-menu"');
})->with('pages');

it('loads only the Arabic fonts on Arabic pages', function (string $url) {
    $this->get($url)
        ->assertSee('font-family: "Cairo"', false)
        ->assertDontSee('"Montserrat"', false)
        ->assertDontSee('"Lora"', false);
})->with('pages');

it('loads only the English fonts on English pages', function (string $url) {
    $this->withSession(['locale' => 'en'])
        ->get($url)
        ->assertSee('font-family: "Montserrat"', false)
        ->assertSee('font-family: "Lora"', false)
        ->assertDontSee('"Cairo"', false);
})->with('pages');

it('loads no fonts from other websites', function (string $url) {
    expect($this->get($url)->getContent())
        ->not->toContain('fonts.googleapis.com')
        ->not->toContain('fonts.gstatic.com')
        ->not->toContain('fonts.bunny.net');
})->with('pages');

it('shows the site menu on visitor pages and the landlord menu on landlord pages', function () {
    $this->get('/saved')
        ->assertSee('المحفوظات')
        ->assertDontSee('للمؤجرين')
        ->assertDontSee('تسجيل الدخول')
        ->assertDontSee('إضافة عقار');

    $this->get('/landlord/housings')
        ->assertSee('إضافة عقار')
        ->assertSee('عقاراتي')
        ->assertDontSee('المحفوظات');
});

it('offers the language and the theme as two toggles under the menu list', function () {
    $this->get('/saved')
        ->assertSeeHtml('aria-label="اللغة"')
        ->assertSeeHtml('name="locale" value="ar"')
        ->assertSeeHtml('name="locale" value="en"')
        ->assertSee('العربية')
        ->assertSee('English')
        ->assertSeeHtml('aria-label="المظهر"')
        ->assertSeeHtml("setTheme('light')")
        ->assertSeeHtml("setTheme('dark')")
        ->assertSee('فاتح')
        ->assertSee('داكن')
        ->assertDontSeeHtml("view = 'language'");

    $this->withSession(['locale' => 'en'])
        ->get('/saved')
        ->assertSee('Light')
        ->assertSee('Dark');
});

it('marks the current language as pressed', function () {
    $html = $this->get('/saved')->getContent();

    expect($html)->toMatch('/value="ar"[^>]*aria-pressed="true"/')
        ->and($html)->toMatch('/value="en"[^>]*aria-pressed="false"/');
});

it('has no back item inside the menu', function () {
    foreach (['/landlord/housings', '/saved', '/'] as $url) {
        $html = $this->get($url)->getContent();

        preg_match('/<nav.*?<\/nav>/s', $html, $nav);

        expect($nav[0])->not->toContain('رجوع')
            ->and($nav[0])->not->toContain('العودة');
    }
});

it('puts one back control under the burger on pages one level down, saying only Back', function (string $url, string $target) {
    $html = $this->get($url)->assertOk()->getContent();

    preg_match_all('/<a[^>]*href="([^"]*)"[^>]*class="fixed start-3 top-20[^"]*"[^>]*>.*?<\/a>/s', $html, $m);

    expect($m[1])->toHaveCount(1)
        ->and(parse_url($m[1][0], PHP_URL_PATH) ?: '/')->toBe($target)
        ->and($m[0][0])->toContain('رجوع')
        ->and($m[0][0])->toContain('<svg')
        ->and($m[0][0])->not->toContain('الرئيسية');

    $this->withSession(['locale' => 'en'])->get($url)->assertSee('Back');
})->with([
    'listings' => ['/listings', '/'],
    'a listing' => ['/listings/1', '/listings'],
    'landlord dashboard' => ['/landlord', '/'],
    'my housings' => ['/landlord/housings', '/landlord'],
    'add housing' => ['/landlord/housings/create', '/landlord/housings'],
]);

it('has no back control on the top-level pages', function (string $url) {
    expect($this->get($url)->getContent())->not->toContain('class="fixed start-3 top-20');
})->with(['/saved', '/me']);

it('draws the burger button in brand black with no background, ring or shadow', function () {
    $html = $this->get('/saved')->getContent();

    preg_match('/<button[^>]*aria-controls="site-menu"[^>]*>/', $html, $match);

    expect($match[0])->toContain('text-content')
        ->not->toContain('bg-')
        ->not->toContain('ring-')
        ->not->toContain('shadow')
        ->not->toContain('rounded')
        ->not->toContain('primary');
});

it('translates the page title', function () {
    $this->get('/listings')->assertSee('<title>السكن المتاح · '.config('app.name').'</title>', false);

    $this->withSession(['locale' => 'en'])
        ->get('/listings/1')
        ->assertSee('<title>Housing details · '.config('app.name').'</title>', false);
});

it('hides what only logged-in users should get behind locked blocks on a listing', function () {
    $this->withSession(['locale' => 'en'])
        ->get('/listings/1')
        ->assertSee('Full address')
        ->assertSee('Shown after you log in')
        ->assertSee('+970', false)
        ->assertSee('+972', false);
});

it('switches the language with the menu form and remembers it', function () {
    $this->from('/saved')->post('/locale', ['locale' => 'en'])->assertRedirect('/saved');

    $this->get('/saved')
        ->assertSee('lang="en"', false)
        ->assertSee('dir="ltr"', false)
        ->assertSee('Saved');
});

it('ignores a language it does not offer', function () {
    $this->from('/saved')->post('/locale', ['locale' => 'fr'])->assertSessionHasErrors('locale');

    $this->get('/saved')->assertSee('lang="ar"', false);
});

it('shows the branded 404 page', function () {
    $this->get('/there-is-no-such-page')
        ->assertNotFound()
        ->assertSee('الصفحة غير موجودة')
        ->assertSee('dir="rtl"', false);
});

it('shows the kit page with the brand pieces', function () {
    $this->get('/_kit')
        ->assertOk()
        ->assertSee('primary-600 (brand)')
        ->assertSee('Phone: prefix first, then the digits')
        ->assertSee('name="kit_phone"', false);
});

it('does not register the skeleton pages in production', function () {
    $result = Process::path(base_path())
        ->env(['APP_ENV' => 'production'])
        ->run([PHP_BINARY, 'artisan', 'route:list', '--json']);

    expect($result->successful())->toBeTrue($result->errorOutput());

    $uris = collect(json_decode($result->output(), true))->pluck('uri')->all();

    expect($uris)->toContain('/')->toContain('locale')
        ->not->toContain('_kit')
        ->not->toContain('listings')
        ->not->toContain('login')
        ->not->toContain('landlord');
});
