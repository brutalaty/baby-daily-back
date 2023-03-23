<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \App\Models\Family;
use \App\Models\User;
use \App\Models\Child;

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
        //\App\Models\User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        //families without the user in it
        Family::factory()->count(5)->create();

        //no children family
        $family = $this->addUserFamily(fake()->lastName(), $user, 'Father');

        //lots of children families
        for ($i = 1; $i < 20; $i++) {
            $family = $this->addUserFamily(fake()->lastName(), $user, 'Father');
            $this->createChildrenForFamily($family);
        }
    }

    private function addUserFamily($familyName, $user, $relation)
    {
        $family = $user->createFamily($familyName, $relation);
        // $family = Family::factory()->create(['name' => $familyName]);
        // $family->adults()->attach($user, ['relation' => $relation]);
        return $family;
    }

    public function createChildrenForFamily($family)
    {
        $count = rand(0, 10);
        for ($i = 0; $i < $count; $i++) {
            $date = fake()->dateTimeBetween('-3 years', '-2 months');
            $dateString = $date->format('Y-m-d');
            $family->addNewChild(
                fake()->firstName() . ' ' . $family->name,
                $dateString
            );
        }
    }
}
