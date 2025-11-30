<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeviceCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle($request, Closure $next)
    {
        $user = $request->user();
        $deviceId = $request->header('X-Device-Id');
    
        if (!$deviceId) {
            return response()->json(['error' => 'device_missing'], 401);
        }
        
        if (!preg_match('/^[A-Za-z0-9_\-\.]{5,200}$/', $deviceId)) {
        return response()->json(['error' => 'device_missings'], 401);
        }

    
        $hashed = hash('sha256', $deviceId);
    
        if ($user->device_id !== $hashed) {
            return response()->json(['error' => 'invalid_device'], 401);
        }
    
        return $next($request);
    }
}
