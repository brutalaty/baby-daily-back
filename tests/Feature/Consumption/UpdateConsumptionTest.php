<?php

namespace Tests\Feature\Child;

use App\Models\Consumption;

use App\Models\User;
use App\Models\Family;
use App\Models\Child;
use App\Models\Activity;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdateConsumptionTest extends TestCase
{
  use DatabaseMigrations;

  protected User $manager;
  protected User $adult;
  protected Family $family;
  protected Child $child;
  protected Activity $activity;
  protected Consumption $consumption;

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

    $this->consumption = $this->activity->consumptions()->save(
      new Consumption([
        'volume' => 50,
        'name' => 'Bread Crumb'
      ])
    );
  }

  /** @test */
  public function a_manager_can_update_a_consumable_for_a_childs_activity_that_is_in_their_family()
  {
    $this->actingAs($this->manager);

    $this->patchJson(
      route('consumptions.update', $this->consumption),
      ['volume' => 100, 'name' => 'Spaghetti']
    )->assertSuccessful();

    $this->consumption->refresh();

    $this->assertEquals(100, $this->consumption->volume);
    $this->assertEquals('Spaghetti', $this->consumption->name);
  }

  /** @test */
  public function a_non_manager_can_update_a_activities_consumption_for_a_child_in_their_family()
  {
    $this->actingAs($this->manager);

    $this->patchJson(
      route('consumptions.update', $this->consumption),
      ['volume' => 100, 'name' => 'Spaghetti']
    )->assertSuccessful();

    $this->consumption->refresh();

    $this->assertEquals(100, $this->consumption->volume);
  }

  /** @test */
  public function a_guest_cannot_update_a_consumption_for_a_childs_activity()
  {
    $this->patchJson(
      route('consumptions.update', $this->consumption),
      ['volume' => 100, 'name' => 'Spaghetti']
    )->assertUnauthorized();
  }

  /** @test */
  public function an_adult_cannot_update_an_activities_consumption_for_a_child_that_is_not_in_their_family()
  {
    $this->actingAs($this->adult);
    $otherChild = Child::factory()->create();
    $otherActivity = Activity::factory()->create(['child_id' => $otherChild->id]);
    $otherConsumption = Consumption::factory()->create(['activity_id' => $otherActivity->id]);

    $this->patchJson(
      route('consumptions.update', $otherConsumption),
      ['volume' => 20, 'name' => 'Garlic Bread']
    )->assertForbidden();
  }


  /** @test */
  public function a_manager_cannot_update_an_activities_consumption_for_a_child_that_is_not_in_their_family()
  {
    $this->actingAs($this->manager);
    $otherChild = Child::factory()->create();
    $otherActivity = Activity::factory()->create(['child_id' => $otherChild->id]);
    $otherConsumption = Consumption::factory()->create(['activity_id' => $otherActivity->id]);

    $this->patchJson(
      route('consumptions.update', $otherConsumption),
      ['volume' => 20, 'name' => 'Garlic Bread']
    )->assertForbidden();
  }

  /** @test */
  public function updating_a_consumption_requires_a_volume()
  {
    $this->actingAs($this->adult);

    $this->patchJson(
      route('consumptions.update', $this->consumption),
      ['name' => 'Garlic Bread']
    )
      ->assertUnprocessable()
      ->assertJsonValidationErrorFor('volume');
  }

  /** @test */
  public function updating_a_consumption_requires_a_name()
  {
    $this->actingAs($this->adult);

    $this->patchJson(
      route('consumptions.update', $this->consumption),
      ['volume' => 50]
    )
      ->assertUnprocessable()
      ->assertJsonValidationErrorFor('name');
  }

  /** @test */
  public function updating_a_consumption_requires_the_volume_to_be_between_0_and_100()
  {
    $this->actingAs($this->adult);

    $this->patchJson(
      route('consumptions.update', $this->consumption),
      ['volume' => 150]
    )
      ->assertUnprocessable()
      ->assertJsonValidationErrorFor('volume');
  }

  /** @test */
  public function when_updating_a_consumption_it_returns_the_updated_consumption()
  {
    $this->actingAs($this->adult);

    $response = $this->patchJson(
      route('consumptions.update', $this->consumption),
      ['volume' => 95, 'name' => 'Pie']
    )
      ->assertSuccessful();

    $response->assertJson(fn (AssertableJson $json) =>
    $json->has(
      'data',
      fn (AssertableJson $json) =>
      $json->where('name', 'Pie')
        ->where('volume', 95)
        ->where('id', $this->consumption->id)
        ->where('activity_id', $this->consumption->activity->id)
        ->etc()
    ));
  }
}
