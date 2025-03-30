<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {

    public function run(): void {
        User::updateOrCreate(
            ['email' => 'admin@expressoftwareclub.com'],
            [
                'name'          => 'Admin',
                'email'         => 'admin@expressoftwareclub.com',
                'password'      => Hash::make('Ts201720#'),
                'type'          => 99,
                'cpfcnpj'       => '50210237000175',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]
        );
    }
}
