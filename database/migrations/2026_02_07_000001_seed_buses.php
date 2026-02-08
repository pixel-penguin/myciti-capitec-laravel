<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Bus;

return new class extends Migration
{
    public function up(): void
    {
        Bus::firstOrCreate(['code' => 'BUS-1'], ['name' => 'Dedicated Bus 1', 'active' => true]);
        Bus::firstOrCreate(['code' => 'BUS-2'], ['name' => 'Dedicated Bus 2', 'active' => true]);
    }

    public function down(): void
    {
        Bus::whereIn('code', ['BUS-1', 'BUS-2'])->delete();
    }
};
