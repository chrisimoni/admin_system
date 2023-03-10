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
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'first_name'    => 'Chris',
            'last_name'     => 'Imoni',
            'email'         => 'imonireal@gmail.com',
            'password'      => Hash::make('password'),
            'role'          => 'admin'
        ]);

        DB::table('users')->insert([
            'first_name'    => 'John',
            'last_name'     => 'Christian',
            'email'         => 'talk2chris2030@yahoo.com',
            'password'      => Hash::make('password'),
            'role'          => 'admin'
        ]);
    }
}
