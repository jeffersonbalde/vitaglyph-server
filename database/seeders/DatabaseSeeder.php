<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->updateOrCreate([
        //     'name' => 'Jefferson Balde',
        //     'email' => 'jeffersonbalde13@gmail.com',
        // ]);

        User::firstOrCreate(
            ['email' => 'jeffersonbalde13@gmail.com'],
            [
                'name' => 'Jefferson Balde',
                'password' => Hash::make('password123')
            ]
        );

    }
}
