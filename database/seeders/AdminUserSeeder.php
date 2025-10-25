<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@newsrewards.com',
            'password' => Hash::make('password123'), // Hash လုပ်ထားရမယ်
            'is_admin' => true,
        ]);
    }
}