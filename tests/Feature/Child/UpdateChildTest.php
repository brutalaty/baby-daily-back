<?php

namespace Tests\Feature\Child;

use App\Models\Child;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;

class UpdateChildTest extends TestCase
{
  use DatabaseMigrations;


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
  public function a_user_can_update_a_child_that_is_related()
  {
    $father = $this->createUser();
    $family = $father->createFamily("Test Family", "Father");
    $child = $family->addNewChild("Test Child", $this->date_string_from_today_subtracting('2 years'));

    $this->actingAs($father);
    $response = $this->patchJson(Route('children.update', $child), ['name' => 'Tube Child']);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'Tube Child');
    $this->assertEquals('Tube Child', $child->fresh()->name);
  }
}
