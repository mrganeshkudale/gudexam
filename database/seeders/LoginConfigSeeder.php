<?php

namespace Database\Seeders;
use App\Models\LoginSettings;
use Illuminate\Database\Seeder;

class LoginConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        LoginSettings::create([
            'description'    => 'Hide Institute Code Textbox On Login Page',
            'action'    => '0',
        ]);
    }
}
