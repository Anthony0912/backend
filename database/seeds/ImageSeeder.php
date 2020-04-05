<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('images')->insert(['name_img' => 'avatar_1.jpg']);
        DB::table('images')->insert(['name_img' => 'avatar_2.jpg']);
        DB::table('images')->insert(['name_img' => 'avatar_3.jpg']);
        DB::table('images')->insert(['name_img' => 'avatar_4.jpg']);
        DB::table('images')->insert(['name_img' => 'avatar_5.jpg']);
    }
}
