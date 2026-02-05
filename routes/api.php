<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\Admin\EmployeeUserController;

/*
|--------------------------------------------------------------------------
| AUTH (PUBLIC)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/me/avatar', [AuthController::class, 'uploadAvatar'])
    ->middleware('auth:sanctum');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED (ADMIN & EMPLOYEE)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});


/*
|--------------------------------------------------------------------------
| ADMIN ONLY
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

    /*
    | EMPLOYEE MANAGEMENT
    */
    Route::apiResource('/employees', EmployeeController::class);
    Route::get('/employee-users', [EmployeeUserController::class, 'index']);
    Route::post('/employee-users', [EmployeeUserController::class, 'store']);
    Route::post('/employee-users/{id}/reset', [EmployeeUserController::class, 'resetPassword']);

    /*
    | PAYROLL MANAGEMENT
    */
    Route::get('/payrolls', [PayrollController::class, 'index']);
    Route::post('/payrolls', [PayrollController::class, 'store']);
    Route::post('/payrolls/{id}/pay', [PayrollController::class, 'pay']);
    Route::put('/payrolls/{id}', [PayrollController::class, 'update']);
    Route::delete('/payrolls/{id}', [PayrollController::class, 'destroy']);
    /*
    | PAYROLL DETAIL & SLIP
    */
    Route::get('/payrolls/{id}/slip', [PayrollController::class, 'slip']);
    Route::get('/payrolls/{id}/slip/pdf', [PayrollController::class, 'slipPdf']);

    /*
    | PAYROLL BY EMPLOYEE
    */
    Route::get(
        '/employees/{id}/payrolls',
        [PayrollController::class, 'byEmployee']
    );

    /*
    | MONTHLY SUMMARY
    */
    Route::get(
        '/payrolls/summary/{month}',
        [PayrollController::class, 'summary']
    );
});

/*
|--------------------------------------------------------------------------
| EMPLOYEE ONLY
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:employee'])->group(function () {

    Route::get('/my-payrolls', [PayrollController::class, 'myPayrolls']);

    Route::get(
        '/my-payrolls/{id}/slip',
        [PayrollController::class, 'mySlip']
    );

    Route::post('/change-password', [AuthController::class, 'changePassword']);

});
