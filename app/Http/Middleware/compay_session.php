<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class compay_session
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        $selectedCompanyId = Session::get('selectedCompanyId') ?? null;
        if ($selectedCompanyId) {
            error_log($selectedCompanyId);
            return $next($request);
        } else {
            error_log("no company selected");
            return redirect()->route('accountingLanding');
        }
    }
}
