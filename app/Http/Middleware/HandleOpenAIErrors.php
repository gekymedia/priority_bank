<?php

namespace App\Http\Middleware;

use Closure;
use OpenAI\Exceptions\ErrorException;

class HandleOpenAIErrors
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($response->exception instanceof ErrorException) {
            return redirect()->back()->withErrors([
                'ai_error' => 'We encountered an issue generating insights. Showing basic analysis instead.'
            ]);
        }

        return $response;
    }
}