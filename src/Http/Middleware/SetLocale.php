<?php

namespace Khudayberdiyev\UzbekistanRegions\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
  public function handle(Request $request, Closure $next): Response
  {
    $default = (string) config('uzbekistan-regions.default_locale', 'uz');
    $locales = (array) config('uzbekistan-regions.locales', ['uz', 'oz', 'ru']);

    $locale = strtolower(explode('-', $request->header('Accept-Language', $default))[0]);
    $locale = in_array($locale, $locales, true) ? $locale : $default;

    app()->setLocale($locale);

    return $next($request);
  }
}
