<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropForeignIdFor('User', 'deleted_by');
            $table->dropColumn('deleted_by');
            $table->dropSoftDeletes();
        });
    }
};
