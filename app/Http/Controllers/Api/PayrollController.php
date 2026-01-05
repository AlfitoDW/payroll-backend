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
     * LIST PAYROLL + FILTER
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
     * CREATE PAYROLL
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
     * BAYAR GAJI
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
     * SLIP GAJI
     */
    public function slip($id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'slip' => [
                'payroll_id'   => $payroll->id,
                'month'        => $payroll->month,
                'status'       => $payroll->status,
                'paid_at'      => $payroll->paid_at,

                'employee' => [
                    'id'       => $payroll->employee->id,
                    'name'     => $payroll->employee->name,
                    'email'    => $payroll->employee->email,
                    'position' => $payroll->employee->position,
                ],

                'salary' => [
                    'basic'      => $payroll->basic_salary,
                    'allowance'  => $payroll->allowance,
                    'deduction'  => $payroll->deduction,
                    'total'      => $payroll->total_salary,
                ]
            ]
        ]);
    }

    /**
     * SLIP GAJI PDF
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
}
