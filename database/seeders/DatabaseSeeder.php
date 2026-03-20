<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@kompeta.test'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('admin12345'),
                'role' => User::ROLE_ADMIN,
                'account_status' => 'active',
                'is_active' => true,
            ]
        );

        $this->call(ExploreDummySeeder::class);
    }
}
