<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $context['context_id'] = Str::ulid()->toString();
        $requestContext['path'] = $request->path();
        $requestContext['method'] = $request->method();

        if (!$request->isMethod('GET')) {
            $requestContext['body'] = $request->except(['email', 'mobile_phone', 'secret_word']);
        }

        $context['request'] = $requestContext;

        if (Auth::check()) {
            $sessionContext = $request->user()->toArray();
            $context['logged_in'] = $sessionContext;
        }

        Log::withContext($context);

        return $next($request);
    }
}
