<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create('es_ES');

        for ($i = 0; $i < 100; $i++) {
            $gender = $faker->randomElement(array('male', 'female'));
            DB::table('clients')->insert([
                'names' => $faker->firstName($gender),
                'lastnames' => $faker->lastName,
                'dni' => $faker->dni,
                'email_corp' => $faker->companyEmail,
            ]);
        }
    }
}
