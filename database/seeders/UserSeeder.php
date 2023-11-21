<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'superadmin@satmz.com',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        DB::table('users')->insert([
            'name' => 'Manager',
            'email' => 'manager@satmz.com',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        DB::table('users')->insert([
            'name' => 'User',
            'email' => 'user@satmz.com',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
    }
}
