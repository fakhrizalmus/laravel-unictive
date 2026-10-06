<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'User Controller']);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validate->errors()
            ], 400);
        }

        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt('password')
        ]);

        return response()->json(['status' => true, 'message' => 'User created successfully', 'user' => $user], 201);
    }

    public function show($id)
    {
        return response()->json(['message' => 'User Controller Show', 'id' => $id]);
    }

    public function update(Request $request, $id) {}

    public function destroy($id)
    {
        return response()->json(['message' => 'User Controller Destroy', 'id' => $id]);
    }

    public function getUserProfile()
    {
        $user = auth()->user();
        return response()->json(['user' => $user]);
    }
}
