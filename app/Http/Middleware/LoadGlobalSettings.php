<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\GlobalSettingService;
use Symfony\Component\HttpFoundation\Response;

class LoadGlobalSettings
{
    protected $globalSettingService;

    public function __construct(GlobalSettingService $globalSettingService)
    {
        $this->globalSettingService = $globalSettingService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) { // Check if a user is authenticated
            $this->globalSettingService->loadGlobalSettings($request->user());
        }

        return $next($request);
    }
}
