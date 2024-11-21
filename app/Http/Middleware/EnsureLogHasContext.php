<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureLogHasContext
{
    /**
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        Log::withContext([
            'context_id' => Str::ulid()->toString(),
            'request' => [
                'path' => $request->path(),
                'method' => $request->method(),
                'body' => $request->except(['email', 'secret_word']),
            ],
        ]);

        return $next($request);
    }
}
