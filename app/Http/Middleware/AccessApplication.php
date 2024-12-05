<?php

namespace App\Http\Middleware;

use Closure;

class AccessApplication
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
        $roleAsCustomer = 'customer';

        if (\Auth::user()->role->name === $roleAsCustomer) {
            \Auth::logout();

            $request->session()->flash('notif', [
                'code' => 'Unauthorized',
                'message' => 'Customer role not allowed to access application',
            ]);
            return redirect()->route('login');
        }
        return $next($request);
    }
}
