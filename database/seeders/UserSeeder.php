<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public const TEST_USER_EMAIL = 'test@example.com';

    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => self::TEST_USER_EMAIL],
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );
    }
}
