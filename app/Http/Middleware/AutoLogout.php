<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class AutoLogout
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $lastActivity = Session::get('lastActivityTime');
            $now = Carbon::now();

            // Check if more than 120 minutes have passed since last activity
            if ($lastActivity && $now->diffInMinutes($lastActivity) > 120) {
                Auth::logout(); // Log the user out
                return redirect('/login')->with('message', 'You have been logged out due to inactivity.');
            }

            // Update last activity time in session
            Session::put('lastActivityTime', $now);
        }

        return $next($request);
    }
}
