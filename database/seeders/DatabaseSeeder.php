<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (\App\Models\Category::count() === 0) {
            User::firstOrCreate(
                ['email' => 'admin@malastreet.com'],
                [
                    'name' => 'เจ้าของร้านหม่าล่าสตรีท (Admin)',
                    'password' => bcrypt('password'),
                ]
            );

            $this->call(MalaStreetSeeder::class);
        }
    }
}
