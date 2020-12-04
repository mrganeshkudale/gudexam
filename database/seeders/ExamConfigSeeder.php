<?php

namespace Database\Seeders;
use App\Models\ExamSettings;
use Illuminate\Database\Seeder;

class ExamConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ExamSettings::create([
            'description'    => 'Enable Global Controller Admin for Examination',
            'action'    => '0',
        ]);

        ExamSettings::create([
            'description'    => 'Enable Cluster Controller Admin for Examination',
            'action'    => '0',
        ]);
    }
}
