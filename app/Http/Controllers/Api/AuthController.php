<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:60',
            'email' => 'required|email:rfc,dns|unique:users',
            'password' => 'required|min:8',
            'no_telp' => 'required|regex:/^08[0-9]{9,11}$/'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400);
        }

        $user = null;
        $index = 0;

        do {
            $index++;
            $temp = now()->format("y.m.") . $index;
            $user = User::find($temp);
        } while ($user != null);

        $registrationData = [
            'id' => $temp,
            'status' => 0,
            'password' => bcrypt($request->password),
        ];

        $user = User::create(array_merge($request->all(), $registrationData));

        return response([
            'message' => 'Register Success, id: ' . $registrationData['id'],
            'user' => $user
        ], 200);
    }

    public function login (Request $request) 
    {
        $loginData = $request->all();
        
        $validate = Validator::make($loginData, [
            'email' => 'required|email:rfc,dns',
            'password' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400);
        }

        if (!Auth::attempt($loginData)) {
            return response(['message' => 'Invalid Credential'], 401);
        }

        /** @var \App\Models\User $user **/
        $user = Auth::user();
        $token = $user->createToken('Authentication Token')->accessToken;

        return response([
            'message' => 'Authenticated',
            'user' => $user,
            'token_type' => 'Bearer',
            'access_token' => $token
        ]);
    }
}