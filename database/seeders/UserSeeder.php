<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database with one user.
     */
    public function run(): void
    {
        $password = config('app.default.user_password');

        // Creates or updates the default (super) admin (userid=1):
        User::updateOrCreate(
            ['id' => 1],
            [
                'id' => 1,
                'slug' => Str::slug(config('app.default.admin_name')),
                'name' => config('app.default.admin_name'),
                'email' => config('app.default.admin_email'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

    }
}
