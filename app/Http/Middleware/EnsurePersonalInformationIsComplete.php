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

        if ($user && method_exists($user, 'isPatient') && $user->isPatient() && ! $this->hasCompletePersonalInfo($user)) {
            if (! $request->routeIs('patient.profile')) {
                return redirect()->route('patient.profile');
            }
        }

        return $next($request);
    }

    private function hasCompletePersonalInfo($user): bool
    {
        $info = $user->personalInformation;

        if (! $info) {
            return false;
        }

        return filled($info->first_name)
            && filled($info->last_name)
            && filled($info->date_of_birth)
            && filled($info->gender);
    }
}
