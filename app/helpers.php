<?php

use MatthiasMullie\Minify;
use Illuminate\Support\Str;

if (!function_exists('asset_min')) {
    function asset_min($path)
    {
        $publicPath = public_path($path);

        if (request()->has('minify')) {
            if (Str::contains($path, '.min.')) {
                return asset($path);
            }

            // CSS
            if (Str::endsWith($path, '.css')) {
                $minPath = Str::replaceLast('.css', '.min.css', $path);
                $minFullPath = public_path($minPath);

                if (!file_exists($minFullPath) || filemtime($minFullPath) < filemtime($publicPath)) {
                    $minifier = new Minify\CSS($publicPath);
                    $minifier->minify($minFullPath);
                }

                return asset($minPath) . '?v=' . filemtime($minFullPath);
            }

            // JS
            if (Str::endsWith($path, '.js')) {
                $minPath = Str::replaceLast('.js', '.min.js', $path);
                $minFullPath = public_path($minPath);

                if (!file_exists($minFullPath) || filemtime($minFullPath) < filemtime($publicPath)) {
                    $minifier = new Minify\JS($publicPath);
                    $minifier->minify($minFullPath);
                }

                return asset($minPath) . '?v=' . filemtime($minFullPath);
            }
        }

        return asset($path);
    }
}
