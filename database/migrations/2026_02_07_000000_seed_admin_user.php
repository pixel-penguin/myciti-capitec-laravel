<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $capitecAdmin = Role::firstOrCreate(['name' => 'capitec_admin']);

        $user = User::firstOrCreate(
            ['email' => 'gerrit+admin@pixel-penguin.com'],
            [
                'name' => 'Gerrit Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
                'two_factor_enabled' => true,
            ]
        );

        $user->assignRole($capitecAdmin);
    }

    public function down(): void
    {
        $user = User::where('email', 'gerrit+admin@pixel-penguin.com')->first();
        if ($user) {
            $user->removeRole('capitec_admin');
            $user->delete();
        }
    }
};
