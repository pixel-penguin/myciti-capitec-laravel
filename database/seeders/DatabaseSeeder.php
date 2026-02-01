<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Bus;
use App\Models\Schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $capitecAdmin = Role::firstOrCreate(['name' => 'capitec_admin']);
        $cityReporter = Role::firstOrCreate(['name' => 'city_reporter']);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user->assignRole($capitecAdmin);

        Bus::firstOrCreate(['code' => 'BUS-1'], ['name' => 'Dedicated Bus 1']);
        Bus::firstOrCreate(['code' => 'BUS-2'], ['name' => 'Dedicated Bus 2']);

        Schedule::firstOrCreate(
            ['name' => 'morning_peak'],
            ['starts_at' => '06:00', 'ends_at' => '09:00']
        );
        Schedule::firstOrCreate(
            ['name' => 'evening_peak'],
            ['starts_at' => '15:00', 'ends_at' => '18:00']
        );
    }
}
