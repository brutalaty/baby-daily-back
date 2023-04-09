<?php

namespace Tests\Feature\Child;

use App\Models\Activity;
use App\Models\User;
use App\Models\Family;
use App\Models\Child;
use App\Models\Consumption;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class DeleteActivityTest extends TestCase
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
    $this->activity = $this->child->activities()->save(new Activity([
      'type' => config('enums.activities.poop'),
      'time' => now()->subMinute()
    ]));
  }

  /** @test */
  public function a_manager_can_delete_an_activity_for_a_child_in_their_family()
  {
    $this->actingAs($this->manager);

    $this->deleteJson(
      route('activities.destroy', $this->activity)
    )->assertSuccessful();

    $this->assertDatabaseMissing('activities', ['id' => $this->activity->id]);
  }


  /** @test */
  public function an_adult_can_delete_an_activity_for_a_child_in_their_family()
  {
    $this->actingAs($this->adult);

    $this->deleteJson(
      route('activities.destroy', $this->activity)
    )->assertSuccessful();

    $this->assertDatabaseMissing('activities', ['id' => $this->activity->id]);
  }

  /** @test */
  public function a_guest_cannot_delete_an_activity()
  {
    $this->deleteJson(
      route('activities.destroy', $this->activity)
    )->assertUnauthorized();
  }

  /** @test */
  public function a_manager_cannot_delete_a_childs_activity_that_is_not_in_the_same_family()
  {
    $otherManager = $this->createUser();
    $this->manager->createFamily(fake()->lastName(), 'Mother');

    $this->actingAs($otherManager);

    $this->deleteJson(
      route('activities.destroy', $this->activity)
    )->assertForbidden();
  }

  /** @test */
  public function an_adult_cannot_delete_a_childs_activity_that_is_not_in_the_same_family()
  {
    $otherAdult = $this->createUser();

    $this->actingAs($otherAdult);

    $this->deleteJson(
      route('activities.destroy', $this->activity)
    )->assertForbidden();
  }
}
