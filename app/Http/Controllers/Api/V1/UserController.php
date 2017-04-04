<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Jobs\SendRegisterEmail;
use App\Transformers\UserTransformer;

class UserController extends BaseController
{
    public function index()
    {
        if ($this->user()->role == 'admin') {
            $users = User::whereIn('role', ['user', 'admin'])->paginate();
        }
        else if ($this->user()->role == 'superadmin') {
            $users = User::paginate();
        }
        else {
            return $this->response->errorUnauthorized();
        }

        return $this->response->paginator($users, new UserTransformer());
    }

    public function show($id)
    {
        $user = User::find($id);

        if (! $user) {
            return $this->response->errorNotFound();
        }

        return $this->response->item($user, new UserTransformer());
    }

    public function userShow()
    {
        return $this->response->item($this->user(), new UserTransformer());
    }

    public function updatePassword(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'old_password' => 'required',
            //'password' => 'required|confirmed|different:old_password',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        $user = $this->user();

        $auth = \Auth::once([
            'email' => $user->email,
            'password' => $request->get('old_password'),
        ]);

        if (! $auth) {
            return $this->response->errorUnauthorized();
        }

        $password = app('hash')->make($request->get('password'));
        $user->update(['password' => $password]);

        return $this->response->item($user, new UserTransformer());
    }

    public function updateProfile(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:50',
            'birthdate'   => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        $user = $this->user();
        $user->updated_at = \Carbon\Carbon::now('Asia/Jakarta');
        $user->name = $request->name;
        $user->birthdate = $request->birthdate;
        $user->save();

        return $this->response->item($user, new UserTransformer());
    }

    public function store(Request $request)
    {
        // forbidden
        if ($this->user()->role == 'user') {
            return $this->response->errorForbidden();
        }

        $validator = \Validator::make($request->all(), [
            'email'       => 'required|email|unique:users',
            'name'        => 'required|min:3',
            'password'    => 'required|confirmed|min:3',
            'birthdate'   => 'nullable|date',
            'role'        => 'required|string',
            'active'      => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        $active = (int)($request->active === 'true');

        $attributes = [
            'email' => $request->get('email'),
            'name' => $request->get('name'),
            'password' => app('hash')->make($request->get('password')),
            'created_at' => \Carbon\Carbon::now('Asia/Jakarta'),
            'updated_at' => \Carbon\Carbon::now('Asia/Jakarta'),
            'birthdate' => $request->birthdate,
            'role' => $request->role,
            'active' => $active
        ];
        $user = User::create($attributes);

        // Send the message after the user has successfully registered
        dispatch(new SendRegisterEmail($user));

        return $this->response->item($user, new UserTransformer());

        /*
        // 201 with location
        $location = dingo_route('v1', 'users.show', $user->id);

        $result = [
            'token' => \Auth::fromUser($user),
            'expired_at' => Carbon::now()->addMinutes(config('jwt.ttl'))->toDateTimeString(),
            'refresh_expired_at' => Carbon::now()->addMinutes(config('jwt.refresh_ttl'))->toDateTimeString(),
        ];

        return $this->response->item($user, new UserTransformer())
            ->header('Location', $location)
            ->setMeta($result)
            ->setStatusCode(201);
        */
    }

    public function update($id, Request $request)
    {
        // forbidden
        if ($this->user()->role == 'user') {
            return $this->response->errorForbidden();
        }

        $user = User::find($id);
        if (! $user) {
            return $this->response->errorNotFound();
        }

        if ($request->password != "") {
          $validator = \Validator::make($request->input(), [
              'email'                 => 'required|min:3|email|unique:users,email,'. $id,
              'name'                  => 'required|min:3|max:100',
              'password'              => 'required|confirmed|min:3',
              'role'                  => 'required|string',
              'birthdate'             => 'nullable|date',
              'active'                => 'required'
          ]);
        } else {
          $validator = \Validator::make($request->all(), [
              'email'                 => 'required|min:3|email|unique:users,email,'. $id,
              'name'                  => 'required|min:3|max:100',
              'role'                  => 'required|string',
              'birthdate'             => 'nullable|date',
              'active'                => 'required'
          ]);
        }

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        $active = (int)($request->active === 'true');

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->birthdate = $request->birthdate;
        $user->active = $active;
        $user->updated_at = \Carbon\Carbon::now('Asia/Jakarta');
        if ($request->password != "") {
            $user->password = app('hash')->make($request->password);
        }
        $user->save();

        return $this->response->item($user, new UserTransformer());
    }

    public function destroy($id)
    {
        // forbidden
        if ($this->user()->role == 'user') {
            return $this->response->errorForbidden();
        }

        $user = User::find($id);

        if (! $user) {
            return $this->response->errorNotFound();
        }

        // $user->delete();
        $user->forceDelete();
        return $this->response->item($user, new UserTransformer());
    }
}
