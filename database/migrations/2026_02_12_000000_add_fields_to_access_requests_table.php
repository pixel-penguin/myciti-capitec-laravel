<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('access_requests', function (Blueprint $table) {
            $table->string('name')->nullable()->after('email');
            $table->string('employee_id')->nullable()->after('name');
            $table->string('department')->nullable()->after('employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('access_requests', function (Blueprint $table) {
            $table->dropColumn(['name', 'employee_id', 'department']);
        });
    }
};
