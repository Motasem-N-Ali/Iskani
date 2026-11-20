<?php

use Illuminate\Support\Facades\Cookie;
use Livewire\Livewire;

it('is Arabic and right to left by default', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('lang="ar"', false)
        ->assertSee('dir="rtl"', false)
        ->assertSee('هل أنت مستأجر أم صاحب عقار؟');
});

it('is English and left to right when the locale is English', function () {
    app()->setLocale('en');

    $this->get('/')
        ->assertOk()
        ->assertSee('lang="en"', false)
        ->assertSee('dir="ltr"', false)
        ->assertSee('Are you a tenant or a landlord?');
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

it('welcomes the visitor with two cards: tenant or landlord', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('هل أنت مستأجر أم صاحب عقار؟')
        ->assertSee('مستأجر')
        ->assertSee('صاحب عقار')
        ->assertSeeHtml("wire:click=\"chooseRole('tenant')\"")
        ->assertSeeHtml("wire:click=\"chooseRole('landlord')\"")
        ->assertDontSee('name="city"', false)
        ->assertDontSee('ابدأ')
        ->assertSee('images/logo/logo-primary.svg', false)
        ->assertSee('images/landing.svg', false)
        ->assertSee('images/ring-primary.svg', false)
        ->assertSee('images/ring-blue-light.svg', false);
});

it('shows an illustration inside each role card', function () {
    $this->get('/')
        ->assertSee('images/landing/tenant.svg', false)
        ->assertSee('images/landing/landlord.svg', false);

    expect(file_exists(public_path('images/landing/tenant.svg')))->toBeTrue()
        ->and(file_exists(public_path('images/landing/landlord.svg')))->toBeTrue();
});

it('takes a tenant to the city dropdown, and back arrow returns to the cards', function () {
    Livewire::test('pages::home')
        ->assertSet('step', 0)
        ->assertDontSeeHtml('wire:click="back"')
        ->call('chooseRole', 'tenant')
        ->assertSet('step', 1)
        ->assertSeeHtml('name="city"')
        ->assertSeeHtml('wire:click="back"')
        ->call('back')
        ->assertSet('step', 0)
        ->assertSet('role', '')
        ->assertDontSeeHtml('wire:click="back"');
});

it('sends a landlord to the landlord area', function () {
    Livewire::test('pages::home')
        ->call('chooseRole', 'landlord')
        ->assertRedirect(route('landlord.dashboard'));
});

it('ignores a role it does not offer', function () {
    Livewire::test('pages::home')
        ->call('chooseRole', 'admin')
        ->assertSet('step', 0)
        ->assertSet('role', '')
        ->assertNoRedirect();
});

it('shows the village step with the back arrow, then returns one step at a time', function () {
    Livewire::test('pages::home')
        ->call('chooseRole', 'tenant')
        ->set('city', 'ramallah')
        ->call('next')
        ->assertSet('step', 2)
        ->assertSeeHtml('wire:click="back"')
        ->assertSee('بيرزيت')
        ->assertDontSee('بيتونيا')
        ->call('back')
        ->assertSet('step', 1);
});

it('will not continue without a valid city', function () {
    Livewire::test('pages::home')
        ->call('chooseRole', 'tenant')
        ->call('next')
        ->assertHasErrors('city')
        ->assertSet('step', 1)
        ->set('city', 'atlantis')
        ->call('next')
        ->assertHasErrors('city')
        ->assertSet('step', 1);
});

it('sends the chosen city and village to the listings', function () {
    Livewire::test('pages::home')
        ->call('chooseRole', 'tenant')
        ->set('city', 'al-bireh')
        ->call('next')
        ->set('village', 'beitunia')
        ->call('search')
        ->assertRedirect(route('listings', ['city' => 'al-bireh', 'village' => 'beitunia']));

    Livewire::test('pages::home')
        ->call('chooseRole', 'tenant')
        ->set('city', 'ramallah')
        ->call('next')
        ->set('village', 'beitunia')
        ->call('search')
        ->assertRedirect(route('listings', ['city' => 'ramallah']));
});

it('shows the light logo at the bottom of the open menu', function () {
    $this->get('/')->assertSee('images/logo/logo-light.svg', false);
});

it('keeps Continue disabled until a city is chosen', function () {
    $submitTag = fn (string $html) => preg_match('/<button[^>]*type="submit"[^>]*>/', $html, $m) ? $m[0] : '';

    $component = Livewire::test('pages::home')->call('chooseRole', 'tenant');
    expect($submitTag($component->html()))->toMatch('/\sdisabled[\s>=]/');

    $component->set('city', 'ramallah');
    expect($submitTag($component->html()))->not->toMatch('/\sdisabled[\s>=]/');
});

it('draws three rings: two up in the corner on the end side, one at the bottom on the start side', function () {
    foreach (['ar', 'en'] as $locale) {
        $html = $this->withSession(['locale' => $locale])->get('/')->getContent();

        preg_match_all('/<img[^>]*images\/ring-[^>]*>/', $html, $rings);

        expect($rings[0])->toHaveCount(3)
            ->and(substr_count($html, 'images/ring-blue-light.svg'))->toBe(1)
            ->and(substr_count($html, 'images/ring-primary.svg'))->toBe(2);

        foreach ($rings[0] as $ring) {
            // start/end, never left/right, so the whole picture mirrors with the language.
            expect(str_contains($ring, '-end-') || str_contains($ring, '-start-'))->toBeTrue();
            expect($ring)->not->toMatch('/(?<![a-z])-?(left|right)-/');
        }

        expect(substr_count(implode('', $rings[0]), 'absolute -end-'))->toBe(2)
            ->and(substr_count(implode('', $rings[0]), 'absolute -bottom-'))->toBe(1)
            ->and(substr_count(implode('', $rings[0]), '-start-'))->toBeGreaterThanOrEqual(1);
    }
});

it('fits one screen and never scrolls on the landing page, but other pages scroll', function () {
    $home = $this->get('/')->getContent();

    expect($home)->toContain('h-dvh overflow-hidden')
        ->and($home)->toMatch('/<body[^>]*overflow-hidden/');

    $other = $this->get('/saved')->getContent();

    expect($other)->not->toMatch('/<body[^>]*overflow-hidden/')
        ->and($other)->toContain('min-h-dvh');
});

it('saves nothing about the visitor on the landing page', function () {
    $response = Livewire::test('pages::home')
        ->call('chooseRole', 'landlord');

    $response->assertRedirect(route('landlord.dashboard'));

    // No cookie is queued, and visiting / again always shows the two cards (no redirect back to the dashboard).
    expect(Cookie::getQueuedCookies())->toBeEmpty();

    $this->get('/')
        ->assertOk()
        ->assertSee('هل أنت مستأجر أم صاحب عقار؟');
});

it('saves nothing when a tenant finishes the steps either', function () {
    Livewire::test('pages::home')
        ->call('chooseRole', 'tenant')
        ->set('city', 'ramallah')
        ->call('next')
        ->set('village', 'birzeit')
        ->call('search');

    expect(Cookie::getQueuedCookies())->toBeEmpty();
});

it('shows the back control with the word Back under the arrow, only from step 1', function () {
    $component = Livewire::test('pages::home')
        ->assertDontSee('رجوع')
        ->call('chooseRole', 'tenant')
        ->assertSee('رجوع')
        ->assertSeeHtml('wire:click="back"');

    expect($component->html())->toMatch('/<button[^>]*wire:click="back"[^>]*>.*arrow-uturn|<button[^>]*wire:click="back"/s');
});

it('sets the theme before the first paint from the saved choice or the system', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain("localStorage.getItem('iskani.theme')")
        ->and($html)->toContain('prefers-color-scheme: dark')
        ->and($html)->toContain('<meta name="color-scheme" content="light dark">');

    // The script runs before the stylesheet, so the page is never drawn in the wrong theme.
    expect(strpos($html, 'iskani.theme'))->toBeLessThan(strpos($html, 'rel="stylesheet"') ?: PHP_INT_MAX);
});

it('shows the light logo on the dark theme and the primary logo on the light theme', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toMatch('/logo-primary\.svg[^>]*dark:hidden/')
        ->and($html)->toMatch('/logo-light\.svg[^>]*hidden[^>]*dark:block/');
});

it('shows the pointer cursor on everything clickable and not-allowed on disabled controls', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toMatch('/button:not\(:disabled\)[^{]*\{\s*cursor: pointer;/s')
        ->and($css)->toContain('a[href]')
        ->and($css)->toContain('select:not(:disabled)')
        ->and($css)->toMatch('/button:disabled[^{]*\{\s*cursor: not-allowed;/s');
});
