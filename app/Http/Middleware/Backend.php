<?php

namespace App\Http\Middleware;

use Closure;
use Auth;


class Backend
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Auth::check()){
			if(Auth::user()->role == 'school' or Auth::user()->role == 'office' or Auth::user()->role == 'admin'){
			return $next($request);
			} else {
				return redirect('/');
			}
		} else {
				return redirect('/');
			}

        
    }
}
