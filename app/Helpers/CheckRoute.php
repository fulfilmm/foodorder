<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class CheckRoute
{
    public static function handleKitchenEntry()
    {
        if (!Auth::check()) {
            return redirect()->route('kitchen.login');
        }

        $user = Auth::user();

        return match ($user->role) {
            'kitchen'  => redirect()->route('kitchen.home'),
            'waiter', 'admin', 'manager' => self::logoutAndRedirectTo('kitchen.login'),
            'customer' => redirect()->route('kitchen.login'),
            default    => redirect()->route('kitchen.login'),
        };
    }
    public static function handleDineInEntry()
    {
        if (!Auth::check()) {
            return redirect()->route('customer.index');
        }

        $user = Auth::user();

        return match ($user->role) {
            'customer' => redirect()->route('customer.die_in.home'),
            'kitchen', 'waiter', 'admin', 'manager' => self::logoutAndRedirectTo('customer.index'),
            default    => redirect()->route('customer.index'),
        };
    }
    public static function handleTakeAwayEntry()
    {
        if (!Auth::check()) {
            return redirect()->route('customer.index');
        }

        $user = Auth::user();

        return match ($user->role) {
            'customer' => redirect()->route('customer.take_away.home'),
            'kitchen', 'waiter', 'admin', 'manager' => self::logoutAndRedirectTo('customer.index'),
            default    => redirect()->route('customer.index'),
        };
    }

    public static function handleWaiterEntry()
    {
        if (!Auth::check()) {
            return redirect()->route('waiter.login');
        }

        $user = Auth::user();

        return match ($user->role) {
            'waiter'  => redirect()->route('waiter.home'),
            'kitchen', 'admin', 'manager' => self::logoutAndRedirectTo('waiter.login'),
            'customer' => redirect()->route('waiter.login'),
            default    => redirect()->route('waiter.login'),
        };
    }
    public static function handleAdminEntry()
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::user();

        return match ($user->role) {
            'admin'  => redirect()->route('admin.home'),
            'kitchen', 'waiter', 'manager' => self::logoutAndRedirectTo('admin.login'),
            'customer' => redirect()->route('admin.login'),
            default    => redirect()->route('admin.login'),
        };
    }
    public static function handleManagerEntry()
    {
        if (!Auth::check()) {
            return redirect()->route('manager.login');
        }

        $user = Auth::user();

        return match ($user->role) {
            'manager'  => redirect()->route('manager.home'),
            'kitchen', 'waiter', 'admin'=> self::logoutAndRedirectTo('manager.login'),
            'customer' => redirect()->route('manager.login'),
            default    => redirect()->route('manager.login'),
        };
    }

    protected static function logoutAndRedirectTo(string $route)
    {
        Auth::guard('web')->logout();
        return redirect()->route($route);
    }
}
//Route::get('/kitchen', function () {
//    if (Auth::check()) {
//        $user = Auth::user();
//
//        // ✅ Redirect based on role
//        switch ($user->role) {
//            case 'kitchen':
//                return redirect()->route('kitchen.home');
//
//            case 'waiter':
//                Auth::guard('web')->logout();
//                return redirect()->route('kitchen.login');
//
//            case 'admin':
//                Auth::guard('web')->logout();
//                return redirect()->route('kitchen.login');
//
//            case 'manager':
//                Auth::guard('web')->logout();
//                return redirect()->route('kitchen.login');
//
//            case 'customer':
//                return redirect()->route('kitchen.login');
//
//            default:
//                return redirect()->route('customer.index'); // fallback or unauthorized
//        }
//    }
//    return redirect()->route('kitchen.login');
//});
