<?php

namespace App\Actions\Fortify;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class CreateLoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user && $user->hasRole('Anggota')) {
            return redirect()->route('home');
        }

        return redirect()->route('dashboard');
    }
}
