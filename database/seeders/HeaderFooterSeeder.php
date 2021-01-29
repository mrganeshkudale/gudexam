<?php

namespace Database\Seeders;
use App\Models\HeaderFooterText;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class HeaderFooterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        HeaderFooterText::create([
            'header'        => 'GudExams',
            'footer'        => 'GudExams',
            'created_at'    => Carbon::now(),
        ]);
    }
}
