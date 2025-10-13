<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
//        return $request->expectsJson() ? null : route('admin.login');
        if ($request->expectsJson()) {
            return null;
        }

        $path = $request->path();

        if (str_starts_with($path, 'waiter')) {
            return route('waiter.login');
        }
        if (str_starts_with($path, 'kitchen')) {
            return route('kitchen.login');
        }
//        elseif (str_starts_with($path, 'manager')) {
//            return route('manager.login');
//        }

        return route('admin.login'); // default
    }

}
