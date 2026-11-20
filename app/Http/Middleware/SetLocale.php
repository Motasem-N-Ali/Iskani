<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Uses the language the visitor chose with the language switch (kept in the session).
 * Without a choice, the app default from APP_LOCALE (Arabic) stays.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->hasSession() ? $request->session()->get('locale') : null;

        if (in_array($locale, config('iskani.locales'), true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
