<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        URL::macro(
            'alternateHasCorrectSignature',
            function (Request $request, $absolute = true, array $ignoreQuery = []) {
                $ignoreQuery[] = 'signature';

                $absoluteUrl = url($request->path());
                $url = $absolute ? $absoluteUrl : '/'.$request->path();

                $queryString = collect(explode('&', (string) $request
                    ->server->get('QUERY_STRING')))
                    ->reject(fn ($parameter) => in_array(Str::before($parameter, '='), $ignoreQuery))
                    ->join('&');

                $original = rtrim($url.'?'.$queryString, '?');

                // Use the application key as the HMAC key
                $key = config('app.key'); // Ensure app.key is properly set in .env

                if (empty($key)) {
                    throw new \RuntimeException('Application key is not set.');
                }

                $signature = hash_hmac('sha256', $original, $key);

                return hash_equals($signature, (string) $request->query('signature', ''));
            }
        );

        URL::macro('alternateHasValidSignature', function (Request $request, $absolute = true, array $ignoreQuery = []) {
            return URL::alternateHasCorrectSignature($request, $absolute, $ignoreQuery)
                && URL::signatureHasNotExpired($request);
        });

        Request::macro('hasValidSignature', function ($absolute = true, array $ignoreQuery = []) {
            return URL::alternateHasValidSignature($this, $absolute, $ignoreQuery);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            // Get APP_URL from config first 
            $appUrl = config('app.url');

            if ($appUrl) {
                $parsedUrl = parse_url($appUrl);
                $scheme = $parsedUrl['scheme'] ?? '';
                $host = $parsedUrl['host'] ?? '';

                // If APP_URL has a valid non-localhost domain, use it
                if ($host && $host !== 'localhost') {
                    // Normalize: ensure https:// scheme for production
                    $normalizedScheme = ($scheme === 'http' || empty($scheme)) ? 'https' : $scheme;
                    $port = isset($parsedUrl['port']) ? ':'.$parsedUrl['port'] : '';
                    $path = $parsedUrl['path'] ?? '';

                    // Use forceRootUrl with the FULL normalized URL (including scheme)
                    // This is the correct way - don't call forceScheme separately to avoid duplication
                    URL::forceRootUrl("{$normalizedScheme}://{$host}{$port}{$path}");
                } elseif ($this->app->runningInConsole() === false) {
                    // Fallback: if APP_URL is localhost, use request host instead
                    $requestHost = $this->app['request']->getHost();
                    if ($requestHost && $requestHost !== 'localhost') {
                        URL::forceRootUrl("https://{$requestHost}");
                    } else {
                        // Last resort: just force HTTPS scheme
                        URL::forceScheme('https');
                    }
                } else {
                    // Console mode and localhost: just force HTTPS
                    URL::forceScheme('https');
                }
            } else {
                // No APP_URL: just force HTTPS
                URL::forceScheme('https');
            }

            $this->app['request']->server->set('HTTPS', true);
        }
    }
}
