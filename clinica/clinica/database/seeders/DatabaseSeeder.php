<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $email = env('SEED_ADMIN_EMAIL', 'test@example.com');
        $password = env('SEED_ADMIN_PASSWORD', 'password');
        $name = env('SEED_ADMIN_NAME', 'Test User');

        $existing = User::where('email', $email)->first();

        if (! $existing) {
            User::create([
                'name' => $name,
                'email' => $email,
                'role' => User::ROLE_ADMIN,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);

            return;
        }

        $updates = [];

        if ($existing->role !== User::ROLE_ADMIN) {
            $updates['role'] = User::ROLE_ADMIN;
        }

        if (is_null($existing->email_verified_at)) {
            $updates['email_verified_at'] = now();
        }

        if (! empty($updates)) {
            $existing->update($updates);
        }
    }
}
