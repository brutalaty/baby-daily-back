<?php

namespace Tests\Feature\Child;

use App\Models\Activity;
use App\Models\User;
use App\Models\Family;
use App\Models\Child;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdateActivityTest extends TestCase
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
  public function a_manager_can_update_an_activity_for_a_child_in_their_family()
  {
    $this->actingAs($this->manager);

    $oldTime = $this->activity->time;

    $response = $this->patchJson(
      route('activities.update', $this->activity),
      ['time' => now()]
    );

    $response->assertSuccessful();
    $this->activity->refresh();

    $this->assertNotEquals($oldTime, $this->activity->time);
  }


  /** @test */
  public function an_adult_can_update_an_activity_for_a_child_in_their_family()
  {
    $this->actingAs($this->adult);

    $oldTime = $this->activity->time;

    $response = $this->patchJson(
      route('activities.update', $this->activity),
      ['time' => now()]
    );

    $this->activity->refresh();
    $response->assertSuccessful();

    $this->assertNotEquals($oldTime, $this->activity->time);
  }

  /** @test */
  public function a_guest_cannot_update_an_activity()
  {
    $this->patchJson(
      route('activities.update', $this->activity),
      ['time' => now()]
    )->assertUnauthorized();
  }

  /** @test */
  public function a_manager_cannot_update_a_childs_activity_that_is_not_in_the_same_family()
  {
    $otherManager = $this->createUser();
    $otherfamily = $this->manager->createFamily(fake()->lastName(), 'Mother');

    $this->actingAs($otherManager);

    $this->patchJson(
      route('activities.update', $this->activity),
      ['time' => now()]
    )->assertForbidden();
  }

  /** @test */
  public function an_adult_cannot_update_a_childs_activity_that_is_not_in_the_same_family()
  {
    $otherAdult = $this->createUser();

    $this->actingAs($otherAdult);

    $this->patchJson(
      route('activities.update', $this->activity),
      ['time' => now()]
    )->assertForbidden();
  }

  /** @test */
  public function an_activities_type_cannot_be_updated()
  {
    $this->actingAs($this->manager);

    $response = $this->patchJson(
      route('activities.update', $this->activity),
      ['time' => now(), 'type' => config('enums.activities.sleep')]
    );

    $response->assertUnprocessable()->assertJsonValidationErrorFor('type');
  }

  /** @test */
  public function when_updating_an_activity_it_requires_a_valid_time()
  {
    $this->actingAs($this->manager);

    $this->patchJson(
      route('activities.update', $this->activity),
      ['time' => 'invalid']
    )->assertJsonValidationErrorFor('time');

    $this->patchJson(
      route('activities.update', $this->activity),
      ['time' => now()]
    )->assertSuccessful();
  }

  /** @test */
  public function when_updating_an_activity_it_returns_with_an_activity_with_correct_date_format()
  {
    $this->actingAs($this->manager);
    $now = now();

    $response = $this->patchJson(
      route('activities.update', $this->activity),
      ['time' => $now]
    )->assertSuccessful();

    $response->assertJsonPath(
      'data.time',
      function ($string) use ($now) {
        return $now->eq(new Carbon($string));
      }
    );
  }

  /** @test */
  public function when_updating_an_activity_it_returns_the_activity_object()
  {
    $this->actingAs($this->manager);

    $response = $this->patchJson(
      route('activities.update', $this->activity),
      ['time' => now()]
    )->assertSuccessful();

    $response->assertJson(fn (AssertableJson $json) =>
    $json->has(
      'data',
      fn (AssertableJson $json) =>
      $json->has('time')
        ->where('type', $this->activity->type)
        ->where('id', $this->activity->id)
        ->where('child_id', $this->activity->child_id)
        ->etc()
    ));
  }
}
