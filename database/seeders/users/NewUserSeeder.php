<?php

namespace Database\Seeders\Users;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \App\Models\User;

class NewUserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'name' => 'Nigel Nofams',
            'email' => 'nigel@example.com',
        ]);
    }
}
