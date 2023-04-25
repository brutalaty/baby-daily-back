<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\Users\ManagerUserSeeder;
use Database\Seeders\Users\NewUserSeeder;
use Database\Seeders\Users\PopularUserSeeder;


use \DateTime;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ManagerUserSeeder::class,
            NewUserSeeder::class,
            PopularUserSeeder::class,
        ]);
    }
}
