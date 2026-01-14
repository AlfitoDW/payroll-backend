<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Str;

class EmployeeUserController extends Controller
{
    public function index()
    {
        return User::with('employee')
            ->where('role', 'employee')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id|unique:users,employee_id',
            'email' => 'required|email|unique:users,email',
        ]);

        $password = Str::random(8);

        $user = User::create([
            'name' => Employee::find($data['employee_id'])->name,
            'email' => $data['email'],
            'password' => bcrypt($password),
            'role' => 'employee',
            'employee_id' => $data['employee_id'],
        ]);

        // (optional) kirim email credential
        // Mail::to($user->email)->send(new EmployeeAccountMail($password));

        return response()->json([
            'message' => 'Akun employee berhasil dibuat',
            'password' => $password, // tampilkan sekali
        ]);
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);

        $password = Str::random(8);
        $user->update([
            'password' => bcrypt($password),
        ]);

        return response()->json([
            'message' => 'Password berhasil direset',
            'password' => $password,
        ]);
    }
}

