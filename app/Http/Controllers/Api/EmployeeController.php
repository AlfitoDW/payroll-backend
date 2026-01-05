<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        return Employee::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:employees',
            'position' => 'required',
            'salary' => 'required|numeric'
        ]);

        $data['user_id'] = $request->user()->id;

        $employee = Employee::create($data);

        return response()->json([
            'message' => 'Employee berhasil ditambahkan',
            'data' => $employee
        ]);
    }

    public function show($id)
    {
        return Employee::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $employee->update($request->all());

        return response()->json([
            'message' => 'Employee berhasil diupdate',
            'data' => $employee
        ]);
    }

    public function destroy($id)
    {
        Employee::destroy($id);

        return response()->json([
            'message' => 'Employee berhasil dihapus'
        ]);
    }
}
