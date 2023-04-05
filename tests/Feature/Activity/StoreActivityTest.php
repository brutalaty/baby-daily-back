<?php

namespace Tests\Feature\Child;

use App\Models\User;
use App\Models\Family;
use App\Models\Child;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;

class StoreActivityTest extends TestCase
{
  use DatabaseMigrations;

  protected User $manager;
  protected User $adult;
  protected Family $family;
  protected Child $child;

  public function setUp(): void
  {
    parent::setUp();
    $this->manager = $this->createUser();
    $this->adult = $this->createUser();
    $this->family = $this->manager->createFamily(fake()->lastName(), 'Father');
    $this->family->addAdult($this->adult, 'Mother');
    $this->child = $this->family->addNewChild(fake()->name(), now()->subYear());
  }

  /** @test */
  public function a_manager_can_create_an_activity_for_a_child_in_their_family()
  {
    $this->actingAs($this->manager);

    $response = $this->postJson(
      route('children.activities.store', $this->child),
      ['time' => now(), 'type' => config('enums.activities.sleep')]
    );

    $response->assertSuccessful();
    $this->child->refresh();

    $this->assertCount(1, $this->child->activities);
  }

  /** @test */
  public function a_non_manager_can_create_an_activity_for_a_child_in_their_family()
  {
    $this->actingAs($this->adult);

    $response = $this->postJson(
      route('children.activities.store', $this->child),
      ['time' => now(), 'type' => config('enums.activities.sleep')]
    );

    $response->assertSuccessful();
    $this->child->refresh();

    $this->assertCount(1, $this->child->activities);
  }

  /** @test */
  public function a_guest_cannot_create_an_activity_for_a_child()
  {
    $this->postJson(
      route('children.activities.store', $this->child),
      ['time' => now(), 'type' => config('enums.activities.sleep')]
    )->assertUnauthorized();
  }

  /** @test */
  public function an_adult_cannot_create_an_activity_for_a_child_that_is_not_in_their_family()
  {
    $this->actingAs($this->adult);
    $otherChild = Child::factory()->create();

    $this->postJson(
      route('children.activities.store', $otherChild),
      ['time' => now(), 'type' => config('enums.activities.sleep')]
    )->assertForbidden();
  }


  /** @test */
  public function a_manager_cannot_create_an_activity_for_a_child_that_is_not_in_their_family()
  {
    $this->actingAs($this->manager);
    $otherChild = Child::factory()->create();

    $this->postJson(
      route('children.activities.store', $otherChild),
      ['time' => now(), 'type' => config('enums.activities.sleep')]
    )->assertForbidden();
  }
}
