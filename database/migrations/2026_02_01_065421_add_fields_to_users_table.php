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
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->default('active')->index();
            $table->string('phone')->nullable();
            $table->foreignId('employee_eligibility_id')->nullable()->constrained('employee_eligibilities')->nullOnDelete();
            $table->timestamp('last_login_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_eligibility_id']);
            $table->dropColumn(['status', 'phone', 'employee_eligibility_id', 'last_login_at']);
        });
    }
};
