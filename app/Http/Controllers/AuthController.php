<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);


        if ($validate->fails()) {
            return response()->json($validate->messages());
        }

        $user = User::create(array_merge($request->all(), [
            'name' => $request['name'],
            'email' => $request['email'],
            'role' => 'admin',
            'password' => Hash::make($request['password']),
        ]));

        if ($user) {
            return response()->json([
                'status' => true,
                'message' => 'Successfully Register'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Failed Register'
            ], 400);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(compact('token'));
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
