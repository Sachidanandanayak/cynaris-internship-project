<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CiaCorrelationIdMiddleware
{
    public const CORRELATION_HEADER = 'X-Correlation-ID';
    public const REQUEST_ID_HEADER = 'X-Request-ID';

    /**
     * Handle an incoming request and ensure uniform correlation ID tracking.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $correlationId = $request->header(self::CORRELATION_HEADER)
            ?: $request->header(self::REQUEST_ID_HEADER)
            ?: (string) Str::uuid();

        // Attach to request attributes for downstream services and controllers
        $request->attributes->set('correlation_id', $correlationId);

        // Share with Monolog global context so all log messages include correlation_id
        if (method_exists(Log::getFacadeRoot(), 'shareContext')) {
            Log::shareContext([
                'correlation_id' => $correlationId,
            ]);
        }

        /** @var Response $response */
        $response = $next($request);

        // Propagate correlation headers back to the client
        $response->headers->set(self::CORRELATION_HEADER, $correlationId);
        $response->headers->set(self::REQUEST_ID_HEADER, $correlationId);

        return $response;
    }
}
