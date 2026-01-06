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

        if ($request->filled('month')) {
            $query->where('month', $request->month);
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

        $data['allowance'] ??= 0;
        $data['deduction'] ??= 0;

        $data['total_salary'] =
            $data['basic_salary']
            + $data['allowance']
            - $data['deduction'];

        $data['status'] = 'pending';

        $payroll = Payroll::create($data);

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
        return response()->json([
            'data' => Payroll::where('employee_id', $request->user()->id)
                ->with('employee')
                ->latest()
                ->get()
        ]);
    }

    /**
     * EMPLOYEE - MY SLIP
     */
    public function mySlip(Request $request, $id)
    {
        $payroll = Payroll::where('id', $id)
            ->where('employee_id', $request->user()->id)
            ->firstOrFail();

        return $this->slipPdf($payroll->id);
    }
}
