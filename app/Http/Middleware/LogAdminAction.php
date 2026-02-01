<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AdminAuditLog;

class LogAdminAction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $user = $request->user();
            if ($user) {
                AdminAuditLog::create([
                    'actor_id' => $user->id,
                    'action' => 'admin.'.$request->method().':'.$request->path(),
                    'target_type' => null,
                    'target_id' => null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'metadata' => [
                        'status' => $response->getStatusCode(),
                    ],
                ]);
            }
        }

        return $response;
    }
}
