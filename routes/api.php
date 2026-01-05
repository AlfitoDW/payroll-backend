<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\PayrollController;
use App\Models\Payroll;


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {


    // EMPLOYEE
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::get('/employees/{id}', [EmployeeController::class, 'show']);
    Route::put('/employees/{id}', [EmployeeController::class, 'update']);
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);

    // PAYROLL
    Route::get('/payrolls', [PayrollController::class, 'index']);
    Route::post('/payrolls', [PayrollController::class, 'store']);
    Route::post('/payrolls/{id}/pay', [PayrollController::class, 'pay']);
    
    // SLIP GAJI
    Route::get('/payrolls/{id}/slip', [PayrollController::class, 'slip']);

    // PAYROLL PER EMPLOYEE
    Route::get('/employees/{id}/payrolls', function ($id) {
        return Payroll::where('employee_id', $id)->get();
    });

    //SLIP GAJI PDF
    Route::get('/payrolls/{id}/slip/pdf', [PayrollController::class, 'slipPdf']);

    // REKAP BULANAN
    Route::get('/payrolls/summary/{month}', function ($month) {
        return response()->json([
            'month' => $month,
            'total_employee' => Payroll::where('month', $month)->count(),
            'total_paid' => Payroll::where('month', $month)->sum('total_salary'),
            'paid' => Payroll::where('month', $month)->where('status','paid')->count(),
            'pending' => Payroll::where('month', $month)->where('status','pending')->count(),
        ]);
    });
});

Route::middleware(['auth:sanctum', 'role:employee'])->group(function () {

    Route::get('/my-payrolls', function (Request $request) {
        return response()->json([
            'data' => $request->user()
                ->payrolls()
                ->with('employee')
                ->latest()
                ->get()
        ]);
    });

    Route::get('/my-payrolls/{id}/slip', function (Request $request, $id) {
        $payroll = $request->user()
            ->payrolls()
            ->where('id', $id)
            ->firstOrFail();

        return app(\App\Http\Controllers\Api\PayrollPdfController::class)
            ->slip($payroll->id);
    });

});

