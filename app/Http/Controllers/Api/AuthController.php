<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role'     => 'required|in:admin,employee', 
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role']
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Register berhasil',
            'user'    => $user,
            'token'   => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        $user = auth()->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => $user
        ]);
    }

    public function me(Request $request)
{
    $user = $request->user();

    if ($user->avatar) {
        $user->avatar = asset('storage/' . $user->avatar);
    }

    return response()->json($user);
}

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

    $data = $request->validate([
        'current_password' => 'required',
        'password' => 'required|min:6|confirmed',
    ]);

    if (!\Hash::check($data['current_password'], $user->password)) {
        return response()->json([
            'message' => 'Password lama salah'
        ], 422);
    }

    $user->update([
        'password' => bcrypt($data['password']),
    ]);

    return response()->json([
        'message' => 'Password berhasil diubah'
    ]);
    }

    public function uploadAvatar(Request $request)
{
    $request->validate([
        'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $user = $request->user();

    if ($user->avatar) {
        \Storage::disk('public')->delete($user->avatar);
    }

    $path = $request->file('avatar')->store('avatars', 'public');

    $user->update([
        'avatar' => $path,
    ]);

    return response()->json([
        'message' => 'Avatar berhasil diupdate',
        'avatar' => asset('storage/' . $path), 
    ]);
}



}
