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
     * @param \Closure(Request): (Response) $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        $context['context_id'] = Str::ulid()->toString();
        $requestContext['path'] = $request->fullUrl();
        $requestContext['method'] = $request->method();

        $shouldLogRequestBody = $request->isMethod('POST')
            || $request->isMethod('PATCH')
            || $request->isMethod('PUT');

        if ($shouldLogRequestBody) {
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
