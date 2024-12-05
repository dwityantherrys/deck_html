<?php

namespace App\Http\Middleware\APIMobile;

use Closure;
use App\User;

class IsAuthanticate
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
        if (empty($request->header('Authorization')))
        {
          return response()->json(['message' => 'unauthorized'], 401);
        }

        $token = explode(" ", $request->header('Authorization'));

        $customer = User::where('token_api', $token[1])->first();
        if ($customer) 
        {
          $request->merge(['user' => $customer ]);
          return $next($request);
        }

        return response()->json(['message' => 'unauthorized'], 401);
    }
}
