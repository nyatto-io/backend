<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use App\Rules\File as FileRule;
use App\Rules\PasswordMatch;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Jenssegers\Agent\Agent;

class AuthController extends Controller
{
    /**
     * Attempt to login a user.
     * 
     * @param \Illuminate\Http\Request $request
     * @param \Jenssegers\Agent\Agent $agent
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request, Agent $agent)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        /**
         * @var \App\Models\User|null
         */
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return response(['message' => 'Email does not exist. Did you mean to register?'], 404);
        }

        if (!$user->hasVerifiedEmail()) {
            return response(['message' => 'You need to verify your email first.'], Response::HTTP_I_AM_A_TEAPOT);
        }

        if (!Hash::check($data['password'], $user->password)) {
            return response(['message' => 'Password is incorrect.'], 403);
        }

        $name = '';

        if ($agent->isMobile() || $agent->isTablet()) {
            $name = $agent->device();
        } else {
            $name = Str::random(10);
        }

        $token = $user->createToken($name);

        return response([
            'user' => $user,
            'token' => $token->plainTextToken,
        ]);
    }

    /**
     * Register a user.
     * 
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $user = User::create($data);

        $user->sendEmailVerificationNotification();

        return response([
            'user' => $user,
        ]);
    }

    /**
     * Log out a user.
     * 
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        /**
         * @var \App\Models\User
         */
        $user = $request->user();

        /**
         * @var \App\Models\Token
         */
        $token = $user->currentAccessToken();

        $token->delete();

        return response('', 204);
    }

    public function profile(Request $request)
    {
        /**
         * @var \App\Models\User
         */
        $user = $request->user();

        $data = $request->validate([
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class, 'email')->ignoreModel($user)],
            'name' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'old_password' => ['nullable', Rule::requiredIf(function () use ($request) {
                return $request->has('password') && is_string($request->input('password'));
            }), 'string', 'max:255', new PasswordMatch($user)],
            'facebook' => ['nullable', 'url', 'max:255'],
            'twitter' => ['nullable', 'url', 'max:255'],
            'youtube' => ['nullable', 'url', 'max:255'],
        ]);

        if (Arr::has($data, 'email') && $user->email !== $data['email']) {
            $user->requests()->create(['email' => $data['email']]);
        }

        $user->update(Arr::except($data, 'email'));

        return $user;
    }

    public function picture(Request $request)
    {
        $raw = $request->validate([
            'file' => ['required', new FileRule()],
        ])['file'];

        $file = File::process($raw);

        $file->save();

        /**
         * @var \App\Models\User
         */
        $user = $request->user();

        $user->profile_picture_id = $file->getKey();

        $user->save();

        $user->load('picture');

        return $user;
    }

    public function resendVerificationEmail(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        if (!Hash::check($data['password'], $user->password)) {
            return response(['message' => 'Password is incorrect.'], 403);
        }

        $user->sendEmailVerificationNotification();

        return response('', 204);
    }

    public function check(Request $request)
    {
        return $request->user();
    }

    public function sendForgotPasswordEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return response(['errors' => [
                'email' => [__($status)]
            ]], 422);
        }

        return response(['status' => __($status)]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->forceFill([
                    'password' => $password,
                ])->save();

                $user->setRememberToken(Str::random(60));

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response(['errors' => [
                'email' => [__($status)]
            ]], 422);
        }

        return response(['status' => __($status)]);
    }
}
