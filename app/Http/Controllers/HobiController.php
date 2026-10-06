<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HobiController extends Controller
{
    public function index()
    {
        $hobi = auth()->user()->hobis()->get();
        return response()->json(['hobis' => $hobi]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_hobi' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 400);
        }

        $hobi = $request->user()->hobis()->create([
            'nama_hobi' => $validator->validated()['nama_hobi'],
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Hobi created successfully',
            'hobi' => $hobi,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $hobi = auth()->user()->hobis()->find($id);

        if (!$hobi) {
            return response()->json([
                'status' => false,
                'message' => 'Hobi not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_hobi' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 400);
        }

        $hobi->update([
            'nama_hobi' => $validator->validated()['nama_hobi'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Hobi updated successfully',
            'hobi' => $hobi,
        ]);
    }
}
