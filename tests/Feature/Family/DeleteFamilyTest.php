<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Family;
use App\Models\Child;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;

class DeleteFamilyTest extends TestCase
{
  use DatabaseMigrations;

  protected User $manager;
  protected User $adult;
  protected Family $family;

  public function setUp(): void
  {
    parent::setUp();
    $this->manager = $this->createUser();
    $this->family = $this->manager->createFamily(fake()->lastName(), 'Father');
    $this->adult = $this->createUser();
    $this->family->addAdult($this->adult, 'Mother');
  }

  /** @test */
  public function a_manager_can_delete_a_family()
  {
    $this->actingAs($this->manager);

    $this->deleteJson(route('families.destroy', $this->family))->assertSuccessful();

    $this->assertModelMissing($this->family);
  }

  /** @test */
  public function a_non_manager_adult_of_a_family_cannot_delete_the_family()
  {
    $this->actingAs($this->adult);

    $this->deleteJson(route('families.destroy', $this->family))->assertForbidden();

    $this->assertModelExists($this->family);
  }

  /** @test */
  public function a_guest_cannot_delete_a_family()
  {
    $this->deleteJson(route('families.destroy', $this->family))->assertUnauthorized();
  }

  /** @test */
  public function a_manager_of_a_family_cannot_delete_another_family()
  {
    $this->actingAs($this->manager);
    $otherFamily = Family::factory()->create();

    $this->deleteJson(route('families.destroy', $otherFamily))->assertForbidden();
  }

  /** @test */
  public function a_family_cannot_be_deleted_if_it_still_has_children()
  {
    $this->actingAs($this->manager);

    $child = $this->family->addNewChild(fake()->name(), now()->subYear(2)->format('Y-m-d'));

    $this->deleteJson(route('families.destroy', $this->family))->assertUnprocessable();

    $this->assertModelExists($this->family);
  }
}
