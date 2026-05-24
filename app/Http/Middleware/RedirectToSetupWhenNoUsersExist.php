<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToSetupWhenNoUsersExist
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (! User::query()->exists()) {
            return redirect()->route('setup.show');
        }

        return $next($request);
    }
}
