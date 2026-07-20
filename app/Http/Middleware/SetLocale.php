<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if a locale is stored in session; if not, detect from the browser
        $locale = Session::get('locale') ?? $this->detectBrowserLocale($request);

        // Persist the detected locale so it doesn't get re-detected every request
        if (! Session::has('locale')) {
            Session::put('locale', $locale);
        }

        // Apply locale globally
        App::setLocale($locale);

        return $next($request);
    }

    protected function detectBrowserLocale(Request $request): string
    {
        $available = config('app.available_locales', [config('app.locale')]);
        $header = $request->server('HTTP_ACCEPT_LANGUAGE');

        if (! $header) {
            return config('app.locale');
        }

        // "de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7" -> ordered list by preference
        $preferred = collect(explode(',', $header))
            ->map(function ($part) {
                $pieces = explode(';q=', trim($part));
                return [
                    'locale' => strtolower(substr(trim($pieces[0]), 0, 2)),
                    'quality' => isset($pieces[1]) ? (float) $pieces[1] : 1.0,
                ];
            })
            ->sortByDesc('quality');

        foreach ($preferred as $lang) {
            if (in_array($lang['locale'], $available, true)) {
                return $lang['locale'];
            }
        }

        return config('app.locale');
    }
}