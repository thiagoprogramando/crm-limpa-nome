<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder {

    public function run(): void {
        User::updateOrCreate(
            ['email' => 'admin@expressoftwareclub.com'],
            [
                'uuid'          => str::uuid(),
                'name'          => 'Admin',
                'email'         => 'admin@expressoftwareclub.com',
                'password'      => Hash::make('123456#'),
                'type'          => 99,
                'cpfcnpj'       => '50210237000175',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]
        );
    }
}
