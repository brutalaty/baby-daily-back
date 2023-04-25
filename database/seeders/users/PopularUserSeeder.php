<?php

namespace Database\Seeders\Users;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \App\Models\Family;
use \App\Models\User;


class PopularUserSeeder extends Seeder
{
  /**
   * Seed the application's database.
   *
   * @return void
   */
  public function run()
  {
    $user = User::factory()->create([
      'name' => 'Randy User',
      'email' => 'randy@example.com',
    ]);

    //Families without the User in it
    Family::factory()->count(2)->create();

    //Child free Family
    $family = $user->createFamily(fake()->lastName(), 'Father');

    //Large Families
    for ($i = 1; $i < 2; $i++) {
      $family = $user->createFamily(fake()->lastName(), 'Father');
      $this->createChildrenForFamily($family, 5, 10);
    }

    //Medium Families
    for ($i = 1; $i < 2; $i++) {
      $family = $user->createFamily(fake()->lastName(), 'Father');
      $this->createChildrenForFamily($family, 3, 5);
    }

    //Small Families
    for ($i = 1; $i < 3; $i++) {
      $family = $user->createFamily(fake()->lastName(), 'Father');
      $this->createChildrenForFamily($family, 1, 3);
    }
  }

  public function createChildrenForFamily($family, $min = 0, $max = 10)
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
