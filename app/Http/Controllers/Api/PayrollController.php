<?php

namespace App\Http\Controllers\Api;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payroll;
use Illuminate\Validation\ValidationException;

class PayrollController extends Controller
{
    /**
     * ADMIN - LIST PAYROLL + FILTER
     */
    public function index(Request $request)
    {
        $query = Payroll::with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('month') && $request->filled('year')) {
        $period = sprintf('%04d-%02d', $request->year, $request->month);
        $query->where('month', $period);
    }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->latest()->get()
        ]);
    }

    /**
     * ADMIN - CREATE PAYROLL
     */
    public function store(Request $request)
{
    $data = $request->validate([
        'employee_id'  => 'required|exists:employees,id',
        'month'        => 'required',
        'basic_salary' => 'required|numeric',
        'allowance'    => 'nullable|numeric',
        'deduction'    => 'nullable|numeric',
    ]);

    $exists = Payroll::where('employee_id', $data['employee_id'])
        ->where('month', $data['month'])
        ->exists();

    if ($exists) {
        throw ValidationException::withMessages([
            'month' => 'Payroll untuk bulan ini sudah ada'
        ]);
    }

    $allowance = (float) ($data['allowance'] ?? 0);
    $deduction = (float) ($data['deduction'] ?? 0);
    $basic = (float) $data['basic_salary'];

    $payroll = Payroll::create([
        'employee_id' => $data['employee_id'],
        'month' => $data['month'],
        'basic_salary' => $basic,
        'allowance' => $allowance,
        'deduction' => $deduction,
        'total_salary' => $basic + $allowance - $deduction,
        'status' => 'pending',
    ]);

    return response()->json([
        'message' => 'Payroll berhasil dibuat',
        'data' => $payroll
    ], 201);
}


    /**
     * ADMIN - PAY SALARY
     */
    public function pay($id)
    {
        $payroll = Payroll::findOrFail($id);

        if ($payroll->status === 'paid') {
            return response()->json([
                'message' => 'Payroll sudah dibayar'
            ], 400);
        }

        $payroll->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);

        return response()->json([
            'message' => 'Gaji berhasil dibayarkan',
            'data' => $payroll
        ]);
    }

    /**
     * ADMIN - SLIP DETAIL (JSON)
     */
    public function slip($id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'slip' => [
                'payroll_id' => $payroll->id,
                'month' => $payroll->month,
                'status' => $payroll->status,
                'paid_at' => $payroll->paid_at,
                'employee' => $payroll->employee,
                'salary' => [
                    'basic' => $payroll->basic_salary,
                    'allowance' => $payroll->allowance,
                    'deduction' => $payroll->deduction,
                    'total' => $payroll->total_salary,
                ]
            ]
        ]);
    }

    /**
     * ADMIN - SLIP PDF
     */
    public function slipPdf($id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);

        $pdf = Pdf::loadView('pdf.slip_gaji', compact('payroll'));

        return $pdf->download(
            'slip-gaji-' .
            $payroll->employee->name .
            '-' .
            $payroll->month .
            '.pdf'
        );
    }

    /**
     * ADMIN - PAYROLL BY EMPLOYEE
     */
    public function byEmployee($id)
    {
        return response()->json([
            'data' => Payroll::where('employee_id', $id)
                ->with('employee')
                ->latest()
                ->get()
        ]);
    }

    /**
     * ADMIN - MONTHLY SUMMARY
     */
    public function summary($month)
    {
        return response()->json([
            'month' => $month,
            'total_employee' => Payroll::where('month', $month)->count(),
            'total_paid' => Payroll::where('month', $month)->sum('total_salary'),
            'paid' => Payroll::where('month', $month)->where('status', 'paid')->count(),
            'pending' => Payroll::where('month', $month)->where('status', 'pending')->count(),
        ]);
    }

    /**
     * EMPLOYEE - MY PAYROLLS
     */
   public function myPayrolls(Request $request)
        {
            $user = $request->user();

            if (!$user->employee_id) {
                return response()->json([
                    'message' => 'Akun employee belum terhubung'
                ], 403);
            }

            return response()->json([
                'data' => Payroll::where('employee_id', $user->employee_id)
                    ->with('employee')
                    ->latest()
                    ->get()
            ]);
        }

    public function update(Request $request, $id)
{
    $payroll = Payroll::findOrFail($id);

    if ($payroll->status === 'paid') {
        return response()->json([
            'message' => 'Payroll yang sudah dibayar tidak bisa diubah'
        ], 403);
    }

    $data = $request->validate([
        'employee_id'  => 'required|exists:employees,id',
        'month'        => 'required',
        'basic_salary' => 'required|numeric',
        'allowance'    => 'nullable|numeric',
        'deduction'    => 'nullable|numeric',
    ]);

    $exists = Payroll::where('employee_id', $data['employee_id'])
        ->where('month', $data['month'])
        ->where('id', '!=', $payroll->id)
        ->exists();

    if ($exists) {
        throw ValidationException::withMessages([
            'month' => 'Payroll untuk bulan ini sudah ada'
        ]);
    }

    $basic     = (float) $data['basic_salary'];
    $allowance = (float) ($data['allowance'] ?? 0);
    $deduction = (float) ($data['deduction'] ?? 0);

    $payroll->update([
        'employee_id' => $data['employee_id'],
        'month' => $data['month'],
        'basic_salary' => $basic,
        'allowance' => $allowance,
        'deduction' => $deduction,
        'total_salary' => $basic + $allowance - $deduction,
    ]);

    return response()->json([
        'message' => 'Payroll berhasil diperbarui',
        'data' => $payroll
    ]);
}


    public function destroy($id)
    {
        $payroll = Payroll::findOrFail($id);

       
        if ($payroll->status === 'paid') {
            return response()->json([
                'message' => 'Payroll yang sudah dibayar tidak bisa dihapus'
            ], 403);
        }

        $payroll->delete();

        return response()->json([
            'message' => 'Payroll berhasil dihapus'
        ]);
    }


    
    /**
     * EMPLOYEE - MY SLIP
     */
    public function mySlip(Request $request, $id)
    {
    $user = $request->user();

        if (!$user->employee_id) {
            abort(403, 'Akun employee belum terhubung');
        }

        $payroll = Payroll::where('id', $id)
            ->where('employee_id', $user->employee_id)
            ->firstOrFail();

        return $this->slipPdf($payroll->id);
    }
}
