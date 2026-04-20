<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@nlstudy.id'],
            [
                'name' => 'Admin NLS',
                'nama_lengkap' => 'Administrator Next Level Study',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );
    }
}
