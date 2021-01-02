<?php

use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('countries')->insert(['name' => 'Alaska', 'code'=> '1907']);
        DB::table('countries')->insert(['name' => 'Argelia', 'code'=> '213']);
        DB::table('countries')->insert(['name' => 'Albania', 'code'=> '355']);
        DB::table('countries')->insert(['name' => 'Argentina', 'code'=> '54']);
        DB::table('countries')->insert(['name' => 'Armenia', 'code'=> '374']);
        DB::table('countries')->insert(['name' => 'Costa Rica', 'code'=> '506']);
        DB::table('countries')->insert(['name' => 'Estados Unidos', 'code'=> '1']);
        DB::table('countries')->insert(['name' => 'El Salvador', 'code'=> '503']);
    }
}
