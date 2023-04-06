<?php

namespace Tests\Feature\Child;

use App\Models\User;
use App\Models\Family;
use App\Models\Child;
use App\Models\Activity;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;

class StoreConsumptionTest extends TestCase
{
  use DatabaseMigrations;

  protected User $manager;
  protected User $adult;
  protected Family $family;
  protected Child $child;
  protected Activity $activity;

  public function setUp(): void
  {
    parent::setUp();
    $this->manager = $this->createUser();
    $this->adult = $this->createUser();
    $this->family = $this->manager->createFamily(fake()->lastName(), 'Father');
    $this->family->addAdult($this->adult, 'Mother');
    $this->child = $this->family->addNewChild(fake()->name(), now()->subYear());

    $this->activity = $this->child->activities()->save(
      new Activity([
        'time' => now(),
        'type' => config('enums.activities.eat')
      ])
    );
  }

  /** @test */
  public function a_manager_can_create_a_consumable_for_a_childs_activity_that_is_in_their_family()
  {
    $this->actingAs($this->manager);

    $response = $this->postJson(
      route('activities.consumptions.store', $this->activity),
      ['volume' => random_int(1, 100), 'name' => 'Spaghetti']
    );

    $response->assertSuccessful();
    $this->activity->refresh();

    $this->assertCount(1, $this->activity->consumptions);
    $this->assertEquals('Spaghetti', $this->activity->consumptions->first()->name);
  }

  /** @test */
  public function a_non_manager_can_create_a_activities_consumption_for_a_child_in_their_family()
  {
    $this->actingAs($this->adult);

    $response = $this->postJson(
      route('activities.consumptions.store', $this->activity),
      ['volume' => random_int(1, 100), 'name' => 'Spaghetti']
    );

    $response->assertSuccessful();
    $this->activity->refresh();

    $this->assertCount(1, $this->activity->consumptions);
  }

  /** @test */
  public function a_guest_cannot_create_a_consumption_for_a_childs_activity()
  {
    $this->postJson(
      route('activities.consumptions.store', $this->activity),
      ['volume' => random_int(1, 100), 'name' => 'Spaghetti']
    )->assertUnauthorized();
  }

  /** @test */
  public function an_adult_cannot_create_an_activities_consumption_for_a_child_that_is_not_in_their_family()
  {
    $this->actingAs($this->adult);
    $otherChild = Child::factory()->create();
    $otherActivity = Activity::factory()->create(['child_id' => $otherChild->id]);

    $this->postJson(
      route('activities.consumptions.store', $otherActivity),
      ['volume' => random_int(1, 100), 'name' => 'Garlic Bread']
    )->assertForbidden();
  }


  /** @test */
  public function a_manager_cannot_create_an_activities_consumption_for_a_child_that_is_not_in_their_family()
  {
    $this->actingAs($this->manager);
    $otherChild = Child::factory()->create();
    $otherActivity = Activity::factory()->create(['child_id' => $otherChild->id]);

    $this->postJson(
      route('activities.consumptions.store', $otherActivity),
      ['volume' => random_int(1, 100), 'name' => 'Garlic Bread']
    )->assertForbidden();
  }

  /** @test */
  public function storing_a_consumption_requires_a_volume()
  {
    $this->actingAs($this->adult);

    $this->postJson(
      route('activities.consumptions.store', $this->activity),
      ['name' => 'Garlic Bread']
    )->assertUnprocessable();
  }

  /** @test */
  public function storing_a_consumption_requires_a_name()
  {
    $this->actingAs($this->adult);

    $this->postJson(
      route('activities.consumptions.store', $this->activity),
      ['volume' => 50]
    )->assertUnprocessable();
  }

  /** @test */
  public function storing_a_consumption_requires_the_volume_to_be_between_0_and_100()
  {
    $this->actingAs($this->adult);

    $this->postJson(
      route('activities.consumptions.store', $this->activity),
      ['name' => 'Cereal', 'volume' => 101]
    )->assertUnprocessable();
  }
}
