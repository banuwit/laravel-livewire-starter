<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
            $table->dropConstrainedForeignId('province_id');
            $table->dropConstrainedForeignId('city_id');
            $table->dropColumn(['gender', 'phonenumber', 'address', 'religion', 'is_active']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'username');
            $table->boolean('is_active')->default(true)->after('password');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('is_active');
            $table->renameColumn('username', 'name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('gender')->nullable();
            $table->string('phonenumber')->nullable();
            $table->string('address')->nullable();
            $table->string('religion')->nullable();
            $table->string('is_active')->default('active');
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('province_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
        });
    }
};
