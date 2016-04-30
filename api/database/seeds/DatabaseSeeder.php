<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::statement("TRUNCATE TABLE users");
        for($i = 0; $i < 500000; $i++){
            $user = new App\User();
            $user->email = 'cuongnxtest@gmail.com';
            $user->phone = '0123456789';
            $user->save();
        }
    }
}
