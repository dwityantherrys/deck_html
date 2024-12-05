<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Gate;
use Closure;

class AccessMenu
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $model)
    {
        if(!Gate::allows('access-menu', $model)) {
            $request->session()->flash('notif', [
                'code' => 'Unauthorized',
                'message' => 'Cannot access menu ' . $model,
            ]);

            return redirect('/');
        }

        return $next($request);
    }
}
