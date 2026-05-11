<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('employee_number')->nullable()->unique()->after('user_id');
            $table->string('birth_place')->nullable()->after('religion');
            $table->date('birth_date')->nullable()->after('birth_place');
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('birth_date');
            $table->text('address')->nullable()->after('marital_status');
            $table->enum('employee_type', ['permanent', 'contract', 'intern', 'parttime'])->nullable()->after('is_active');
            $table->date('join_date')->nullable()->after('employee_type');
            $table->date('end_date')->nullable()->after('join_date');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'employee_number', 'birth_place', 'birth_date',
                'marital_status', 'address',
                'employee_type', 'join_date', 'end_date',
            ]);
        });
    }
};
