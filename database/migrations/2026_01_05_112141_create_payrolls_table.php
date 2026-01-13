<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
        $table->id();

        $table->foreignId('employee_id')
            ->constrained()
            ->onDelete('cascade');

        $table->string('month'); 

        
        $table->unsignedBigInteger('basic_salary');
        $table->unsignedBigInteger('allowance')->default(0);
        $table->unsignedBigInteger('deduction')->default(0);
        $table->unsignedBigInteger('total_salary');

        $table->string('status')->default('pending'); 
        $table->timestamp('paid_at')->nullable();

        $table->timestamps();
    });

    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
