<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user?->isPatient()) {
            if (! $this->hasCompletePersonalInfo($user)) {
                return redirect()->route('patient.profile');
            }

            return redirect()->intended(route('patient.dashboard'));
        }

        if ($user?->isNurse()) {
            return redirect()->intended(route('nurse.dashboard'));
        }

        if ($user?->isDoctor()) {
            return redirect()->intended(route('doctor.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
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
