<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = User::all();

        $response = (count($user) > 0)
            ? ['message' => 'Retrieve All Success', 'data' => $user]
            : ['message' => 'Empty', 'data' => null];

        return response($response, (count($user) > 0) ? 200 : 400);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::find($id);

        $response = (!is_null($user))
            ? ['message' => 'User found', 'data' => $user]
            : ['message' => 'User Not Found', 'data' => null];

        return response($response, (!is_null($user)) ? 200 : 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $updateData = $request->all();
        $user = User::find($id);

        if (is_null($user)) {
            return response(['message' => 'User not found', 'data' => null], 404);
        }

        $validate = Validator::make($updateData, [
            'name' => 'required|max:60',
            'email' => 'required|email:rfc,dns|unique:users,email,' . $id,
            'password' => 'required|min:8',
            'no_telp' => 'required|regex:/^08[0-9]{9,11}$/'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400);
        }

        $user->name = $updateData['name'];
        $user->email = $updateData['email'];
        $user->password = $updateData['password'];
        $user->no_telp = $updateData['no_telp'];

        if ($user->save()) {
            return response([
                'message' => 'Update User Success',
                'data' => $user,
            ], 200);
        }

        return response([
            'message' => 'Update User Failed',
            'data' => null,
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        $response = (is_null($user))
            ? ['message' => 'User Not Found', 'data' => null]
            : ($user->delete()
                ? ['message' => 'Delete User Success', 'data' => $user]
                : ['message' => 'Delete User Failed', 'data' => null]);

        return response($response, (is_null($user)) ? 404 : 200);
    }
}