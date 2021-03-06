<?php

namespace App\Guards;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Guard;
use Laravel\Sanctum\TransientToken;
use Laravel\Sanctum\Sanctum as SanctumMain;

class Sanctum extends Guard
{
    /**
     * Retrieve the authenticated user for the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        foreach (Arr::wrap(config('sanctum.guard', 'web')) as $guard) {
            if ($user = $this->auth->guard($guard)->user()) {
                return $this->supportsTokens($user)
                    ? $user->withAccessToken(new TransientToken)
                    : $user;
            }
        }

        if ($token = $this->getToken($request)) {
            $model = SanctumMain::$personalAccessTokenModel;

            $accessToken = $model::findToken($token);

            if (
                !$accessToken ||
                ($this->expiration &&
                    $accessToken->created_at->lte(now()->subMinutes($this->expiration))) ||
                !$this->hasValidProvider($accessToken->tokenable)
            ) {
                return;
            }

            return $this->supportsTokens($accessToken->tokenable) ? $accessToken->tokenable->withAccessToken(
                tap($accessToken->forceFill(['last_used_at' => now()]))->save()
            ) : null;
        }
    }

    protected function getToken(Request $request)
    {
        if ($token = $request->bearerToken()) {
            return $token;
        }
        if ($request->has('token')) {
            return $request->get('token');
        }

        return null;
    }
}
