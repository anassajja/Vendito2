<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([ //insert data into users table
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'cnie' => '12345678',
            'phone' => '0712345678',
            'address' => '123 Main Street',
            'city' => 'Casablanca',
            'birthdate' => '1990-01-01',
            'avatar' => "1714341150.png",
            'email' => 'admin001@gmail.com',
            'password' => Hash::make('password123$@@'), // Hash the password
            'role' => 'admin',
            'status' => 'active',
            'is_admin' => true,
        ]);
    }
}
