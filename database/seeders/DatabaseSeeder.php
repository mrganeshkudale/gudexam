<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTablesSeeder::class);
        $this->call(LoginConfigSeeder::class);
        $this->call(ExamConfigSeeder::class);
        $this->call(HeaderFooterSeeder::class);
    }
}
