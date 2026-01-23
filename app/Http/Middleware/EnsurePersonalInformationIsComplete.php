<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePersonalInformationIsComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && method_exists($user, 'isPatient') && $user->isPatient() && ! $user->hasCompletePersonalInformation()) {
            if (! $request->routeIs('patient.profile')) {
                return redirect()
                    ->route('patient.profile')
                    ->with('profile_incomplete', __('Please complete your profile to continue.'));
            }
        }

        return $next($request);
    }

}
