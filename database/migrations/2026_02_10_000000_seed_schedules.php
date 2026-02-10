<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Schedule;

return new class extends Migration
{
    public function up(): void
    {
        Schedule::firstOrCreate(['name' => 'Morning Route'], ['starts_at' => '06:00', 'ends_at' => '09:00', 'active' => true]);
        Schedule::firstOrCreate(['name' => 'Evening Route'], ['starts_at' => '16:00', 'ends_at' => '19:00', 'active' => true]);
    }

    public function down(): void
    {
        Schedule::whereIn('name', ['Morning Route', 'Evening Route'])->delete();
    }
};
