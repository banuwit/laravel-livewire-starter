<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete()->after('user_id');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->after('company_id');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
