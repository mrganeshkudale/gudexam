<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'username'          => 'admin',
            'role'              => 'ADMIN',
            'password'          =>  Hash::make('gudexam'),
            'origpass'          => 'gudexam',
            'name'              => 'admin',
            'status'            => 'ON',
            'regi_type'         => 'Admin',
            'verified'          => 'verified',
        ]);
    }
}
