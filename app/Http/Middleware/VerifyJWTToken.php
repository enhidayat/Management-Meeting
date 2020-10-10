<?php

namespace App\Http\Middleware;

use Closure;

// use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth as JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
// use Tymon\JWTAuth\JWTAuth;



class VerifyJWTToken
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
        //chek apakah saat mengakses route ke controllaer menggunakan token dan token masif aktif tidak
        try {
            $user = JWTAuth::toUser($request->input('token'));
            // $user = JWTAuth::toUser($request->header('token'));
            // $user = JWTAuth::toUser($token);

            // return response()->json(compact('token', 'user'));
            // $user = JWTAuth::toUser($token);
            $user = $user->id;
        } catch (JWTException $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['token_expired'], $e->getCode());
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\InvalidClaimException) {
                return response()->json(['token_invalid'], $e->getCode());
            } else {
                return response()->json(['error' => 'Token is required']);
            }
        }

        return $next($request);
    }
}
