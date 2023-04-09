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

class DeleteConsumptionTest extends TestCase
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

    $this->activity = Activity::factory()->create(['child_id' => $this->child->id]);

    $this->consumption = Consumption::factory()->create(['activity_id' => $this->activity->id]);
  }

  /** @test */
  public function a_families_manager_can_delete_a_consumption()
  {
    $this->actingAs($this->manager);

    $this->deleteJson(route('consumptions.destroy', $this->consumption))->assertSuccessful();

    $this->assertModelMissing($this->consumption);
  }

  /** @test */
  public function an_adult_can_delete_an_activities_consumption_that_belongs_to_the_same_family()
  {
    $this->actingAs($this->adult);

    $this->deleteJson(route('consumptions.destroy', $this->consumption))->assertSuccessful();

    $this->assertModelMissing($this->consumption);
  }

  /** @test */
  public function a_guest_cannot_delete_consumptions()
  {
    $this->deleteJson(route('consumptions.destroy', $this->consumption))->assertUnauthorized();

    $this->assertModelExists($this->consumption);
  }

  /** @test */
  public function a_manager_cannot_delete_a_consumption_that_belongs_to_another_family()
  {
    $consumption = Consumption::factory()->create();

    $this->actingAs($this->manager);

    $this->deleteJson(route('consumptions.destroy', $consumption))->assertForbidden();

    $this->assertModelExists($consumption);
  }

  /** @test */
  public function when_deleting_an_activity_it_deletes_its_consumptions()
  {
    $this->actingAs($this->manager);

    $this->deleteJson(route('activities.destroy', $this->activity))->assertSuccessful();

    $this->assertModelMissing($this->consumption);
  }
}
