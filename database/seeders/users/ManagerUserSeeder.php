<?php

namespace Database\Seeders\Users;

use Illuminate\Database\Seeder;
use \App\Models\User;
use \App\Models\Family;
use \App\Models\Activity;
use \App\Models\Consumption;

class ManagerUserSeeder extends Seeder
{
  /**
   * Seed the application's database.
   *
   * @return void
   */
  public function run()
  {
    //\App\Models\User::factory(10)->create();

    $manager = User::factory()->create([
      'name' => 'Da Boss',
      'email' => 'manager@example.com',
    ]);
    $adult = User::factory()->create(
      ['name' => 'Grace Boss', 'email' => 'adult@example.com']
    );

    $family = $manager->createFamily('Boss', 'Father');

    $family->addAdult($adult, 'Mother');

    $this->addChildWithActivities($family);
    $this->addChildWithoutActivities($family);
  }

  private function addChildWithoutActivities(Family $family)
  {
    $family->addNewChild(fake()->firstName() . ' Boss', now()->subMonth(8)->subDays(3));
  }

  private function addChildWithActivities(Family $family): void
  {
    $child = $family->addNewChild(fake()->firstName() . ' Boss', now()->subYear(2));
    $child->activities()->saveMany([
      new Activity([
        'type' => config('enums.activities.wake'),
        'time' => $this->today(6),
      ]),
      new Activity([
        'type' => config('enums.activities.poop'),
        'time' => $this->today(9, 15),
      ]),
      new Activity([
        'type' => config('enums.activities.sleep'),
        'time' => $this->today(11, 30),
      ]),
      new Activity([
        'type' => config('enums.activities.wake'),
        'time' => $this->today(13, 30),
      ]),
      new Activity([
        'type' => config('enums.activities.poop'),
        'time' => $this->today(15, 47),
      ]),
      new Activity([
        'type' => config('enums.activities.sleep'),
        'time' => $this->today(19, 45),
      ]),
    ]);

    //add feeding activities
    $child->addNewActivity(config('enums.activities.eat'), $this->today(7))
      ->consumptions()->save(
        new Consumption(['name' => 'Pancakes', 'volume' => 90])
      );

    $child->addNewActivity(config('enums.activities.eat'), $this->today(10))
      ->consumptions()->saveMany([
        new Consumption(['name' => 'Musli Bar', 'volume' => 100]),
        new Consumption(['name' => 'Apple', 'volume' => 50])
      ]);

    $child->addNewActivity(config('enums.activities.eat'), $this->today(15))
      ->consumptions()->saveMany([
        new Consumption(['name' => 'Garlic Bread', 'volume' => 100]),
        new Consumption(['name' => 'Spaghetti', 'volume' => 30])
      ]);

    $child->addNewActivity(config('enums.activities.eat'), $this->today(19))
      ->consumptions()->saveMany([
        new Consumption(['name' => 'Milk', 'volume' => 100])
      ]);

    //add a medicine activity
    $child->addNewActivity(config('enums.activities.medicine'), $this->today(22, 10))
      ->consumptions()->save(
        new Consumption(['name' => 'Ibuprophen', 'volume' => 100])
      );
  }

  private function today(int $hours, int $minutes = 0)
  {
    return today()->addHours($hours)->addMinutes($minutes);
  }
}
