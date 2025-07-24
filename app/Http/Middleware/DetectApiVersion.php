<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DetectApiVersion
{
public function handle(Request $request, Closure $next)
{
// Example: Detect version from URL prefix or headers
// Assuming version in URL: /api/v1/... or /api/v2/...
$version = $request->segment(2); // e.g., 'v1' or 'v2'

// Store version in request attribute
$request->attributes->set('api_version', $version);

return $next($request);
}
}
