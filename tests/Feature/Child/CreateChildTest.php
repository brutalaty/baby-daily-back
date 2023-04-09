<?php

namespace Tests\Feature\Child;

use App\Models\User;
use App\Models\Family;
use App\Models\Child;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CreateChildTest extends TestCase
{
  use DatabaseMigrations;

  private $dateInThePast;

  public function setUp(): void
  {
    parent::setUp();

    $this->dateInThePast = now()->subYear(2)->format('Y-m-d');
  }

  /** @test */
  public function a_families_manager_can_create_children()
  {
    $me = $this->createUser();
    $myFamily = $me->createFamily(fake()->lastName(), 'Father');

    $this->actingAs($me);

    $response = $this->postJson(route('families.children.store', $myFamily), [
      'name' => 'test child',
      'born' => $this->dateInThePast,
    ]);

    $response->assertSuccessful();

    $this->assertCount(1, $myFamily->fresh()->children);
  }

  /** @test */
  public function a_guest_cannot_create_a_child()
  {
    $family = Family::factory()->create();

    $response = $this->postJson(route('families.children.store', $family), [
      'name' => 'test child',
      'born' => $this->dateInThePast,
    ]);

    $response->assertUnauthorized();
  }

  /** @test */
  public function a_user_cannot_create_a_child_for_a_family_they_do_not_belong_to()
  {
    $this->actingAs($this->createUser());
    $otherFamily = Family::factory()->create();

    $response = $this->postJson(route('families.children.store', $otherFamily), [
      'name' => 'test child',
      'born' => $this->dateInThePast,
    ]);

    $response->assertForbidden();
  }

  /** @test */
  public function a_user_cannot_create_a_child_for_a_family_they_belong_to()
  {
    $myFamily = Family::factory()->create();
    $me = $this->createUser();
    $myFamily->addAdult($me, 'Father');
    $this->actingAs($me);

    $this->postJson(route('families.children.store', $myFamily), [
      'name' => 'test child',
      'born' => $this->dateInThePast,
    ])->assertForbidden();
  }
}
