<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentLanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
		
		// app/Helpers/LocaleHelper.php

namespace App\Helpers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Lang;

class LocaleHelper
{
    public static function translate($key, array $replace = [], $locale = null)
    {
        $headerLocale = Request::header('Content-Language');
        
        if ($headerLocale && !empty($headerLocale)) {
            $locales = explode(',', $headerLocale);
            foreach ($locales as $locale) {
                $locale = trim($locale);
                if (file_exists(resource_path('lang/' . $locale . '/' . Lang::getFilePath($key)))) {
                    return trans($key, $replace, $locale);
                }
            }
        }

        // Fallback to default translation
        return trans($key, $replace);
    }
}

		
        return $next($request);
    }
}
