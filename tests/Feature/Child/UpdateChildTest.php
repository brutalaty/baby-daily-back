<?php

namespace Tests\Feature\Child;

use App\Models\Child;
use App\Models\Family;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;

class UpdateChildTest extends TestCase
{
  use DatabaseMigrations;


  /** @test */
  public function a_manager_of_a_family_can_update_a_child_of_that_family()
  {
    $father = $this->createUser();
    $family = $father->createFamily(fake()->lastName(), "Father");
    $child = $family->addNewChild("Test Child", now()->subYear(2));

    $this->actingAs($father);
    $response = $this->patchJson(Route('children.update', $child), ['name' => 'Tube Child']);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'Tube Child');
    $this->assertEquals('Tube Child', $child->fresh()->name);
  }

  /** @test */
  public function a_guest_cannot_update_a_child()
  {
    $child = Child::factory()->create(['name' => 'Test Child']);

    $response = $this->getJson(Route('children.update', $child), [
      'name' => 'Tube Child'
    ]);

    $response->assertUnauthorized();
  }

  /** @test */
  public function a_user_cannot_update_a_child_that_is_of_no_relation()
  {
    $this->actingAs($this->createUser());
    $child = Child::factory()->create(['name' => 'Test Child']);

    $response = $this->getJson(Route('children.update', $child), [
      'name' => 'Tube Child'
    ]);

    $response->assertForbidden();
  }

  /** @test */
  public function a_user_cannot_update_a_child_that_is_related()
  {
    $family = Family::factory()->create();
    $father = $this->createUser();
    $family->addAdult($father, 'Father');
    $child = $family->addNewChild("Test Child", now()->subYear(2));

    $this->actingAs($father);

    $this->patchJson(Route('children.update', $child), ['name' => 'Tube Child'])->assertForbidden();
  }
}
