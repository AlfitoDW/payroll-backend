<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * GET /api/employees
     */
    public function index()
    {
        return response()->json([
            'data' => Employee::all()
        ]);
    }

    /**
     * POST /api/employees
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:employees,email',
            'position' => 'required|string|max:255',
            'salary'   => 'required|numeric',
        ]);

        $data['user_id'] = $request->user()->id;

        $employee = Employee::create($data);

        return response()->json([
            'message' => 'Employee berhasil ditambahkan',
            'data'    => $employee
        ], 201);
    }

    /**
     * GET /api/employees/{id}
     */
    public function show($id)
    {
        return response()->json([
            'data' => Employee::findOrFail($id)
        ]);
    }

    /**
     * PUT /api/employees/{id}
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:employees,email,' . $employee->id,
            'position' => 'required|string|max:255',
            'salary'   => 'required|numeric',
        ]);

        $employee->update($data);

        return response()->json([
            'message' => 'Employee berhasil diupdate',
            'data'    => $employee
        ]);
    }

    /**
     * DELETE /api/employees/{id}
     */
    public function destroy($id)
    {
        Employee::destroy($id);

        return response()->json([
            'message' => 'Employee berhasil dihapus'
        ]);
    }
}
