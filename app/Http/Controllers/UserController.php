<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function home()
    {
        $users = User::with('hobis')
            ->where('role', 'user')
            ->orderBy('name')
            ->get();

        return view('index', compact('users'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'hobis' => ['required', 'array', 'min:1'],
            'hobis.*.nama_hobi' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 400);
        }

        $validated = $validator->validated();

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => 'user',
                'password' => 'password',
            ]);

            $user->hobis()->createMany($validated['hobis']);

            return $user->load('hobis');
        });

        return response()->json([
            'status' => true,
            'message' => 'User and hobbies created successfully',
            'user' => $user,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'hobis' => ['sometimes', 'array'],
            'hobis.*.id' => [
                'nullable',
                'integer',
                'distinct',
                Rule::exists('hobis', 'id')->where('user_id', $user->id),
            ],
            'hobis.*.nama_hobi' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 400);
        }

        $validated = $validator->validated();

        $user = DB::transaction(function () use ($user, $validated) {
            $user->fill(array_intersect_key($validated, array_flip(['name', 'email'])));
            $user->save();

            if (array_key_exists('hobis', $validated)) {
                $hobiIds = [];

                foreach ($validated['hobis'] as $hobiData) {
                    if (!empty($hobiData['id'])) {
                        $hobi = $user->hobis()->whereKey($hobiData['id'])->firstOrFail();
                        $hobi->update(['nama_hobi' => $hobiData['nama_hobi']]);
                    } else {
                        $hobi = $user->hobis()->create([
                            'nama_hobi' => $hobiData['nama_hobi'],
                        ]);
                    }

                    $hobiIds[] = $hobi->id;
                }

                if ($hobiIds === []) {
                    $user->hobis()->delete();
                } else {
                    $user->hobis()->whereNotIn('id', $hobiIds)->delete();
                }
            }

            return $user->load('hobis');
        });

        return response()->json([
            'status' => true,
            'message' => 'User and hobbies updated successfully',
            'user' => $user,
        ]);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        DB::transaction(function () use ($user) {
            $user->hobis()->delete();
            $user->delete();
        });

        return response()->json([
            'status' => true,
            'message' => 'User and hobbies deleted successfully',
        ]);
    }
}
