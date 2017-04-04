<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        // check user is active or not
        $user = User::where([
                    ['email', '=', $request->email],
                    ['active', '=', 1]
                ])->first();

        if (!$user) {
          return $this->response->errorUnauthorized();
        }

        $credentials = $request->only('email', 'password');
        // Validation failed to return 403
        if (! $token = \Auth::attempt($credentials)) {
            $this->response->errorUnauthorized(trans('auth.incorrect'));
        }

        $result['data'] = [
            'user' => $user,
            'token' => $token,
            'expired_at' => Carbon::now('Asia/Jakarta')->addMinutes(config('jwt.ttl'))->toDateTimeString(),
            'refresh_expired_at' => Carbon::now('Asia/Jakarta')->addMinutes(config('jwt.refresh_ttl'))->toDateTimeString(),
        ];

        return $this->response->array($result)->setStatusCode(201);
    }

    public function register(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'email' => 'required|email|unique:users',
            'name' => 'required|string',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        $password = $request->get('password');

        $attributes = [
            'email' => $request->get('email'),
            'name' => $request->get('name'),
            'password' => app('hash')->make($password),
            'role' => 'user',
            'active' => 1 // for test, lets activate automatically
        ];
        $user = User::create($attributes);

        $credentials = $request->only('email', 'password');

        // Validation failed to return 403
        if (! $token = \Auth::attempt($credentials)) {
            $this->response->errorUnauthorized(trans('auth.incorrect'));
        }

        $result['data'] = [
            'user' => $user,
            'token' => $token,
            'expired_at' => Carbon::now('Asia/Jakarta')->addMinutes(config('jwt.ttl'))->toDateTimeString(),
            'refresh_expired_at' => Carbon::now('Asia/Jakarta')->addMinutes(config('jwt.refresh_ttl'))->toDateTimeString(),
        ];

        return $this->response->array($result)->setStatusCode(201);
    }

    public function update()
    {
        // check token
        // same with \Auth::requireToken()->checkOrFail();
        // \Auth::getPayload();

        $result['data'] = [
            'token' => \Auth::refresh(),
            'expired_at' => Carbon::now()->addMinutes(config('jwt.ttl'))->toDateTimeString(),
            'refresh_expired_at' => Carbon::now()->addMinutes(config('jwt.refresh_ttl'))->toDateTimeString(),
        ];

        return $this->response->array($result);
    }

    public function destroy()
    {
        \Auth::logout();
        return $this->response->noContent();
    }
}
