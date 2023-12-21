<?php

namespace App\Http\Middleware;

use Closure;

class Superadmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $administrator_list = config('constants.administrator_usernames');

        $isSuperAdmin = $request->user()->user_type == 'superadmin';
        if (($isSuperAdmin) || (!empty($request->user()) && in_array(strtolower($request->user()->username), explode(',', strtolower($administrator_list))))) {
            return $next($request);
        } else {
           //temp  abort(403, 'Unauthorized action.');
        }
    }
}
