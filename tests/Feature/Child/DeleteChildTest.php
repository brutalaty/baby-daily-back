<?php

namespace Tests\Feature\Child;

use App\Models\User;
use App\Models\Family;
use App\Models\Child;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Route;

use Tests\TestCase;

class DeleteChildTest extends TestCase
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
  public function managers_can_delete_children_from_their_family()
  {
    $this->actingAs($this->manager);

    $this->assertTrue($this->family->children->contains($this->child));

    $this->deleteJson(route('children.destroy', $this->child));

    $this->assertModelMissing($this->child);
  }

  /** @test */
  public function non_managers_cannot_delete_children()
  {
    $this->actingAs($this->adult);
    $this->deleteJson(route('children.destroy', $this->child))->assertForbidden();
  }

  /** @test */
  public function guests_cannot_delete_children()
  {
    $this->deleteJson(route('children.destroy', $this->child))->assertUnauthorized();
  }

  /** @test */
  public function a_manager_cannot_delete_children_from_other_families()
  {
    $this->actingAs($this->manager);
    $otherFamily = $this->adult->createFamily(fake()->lastName(), 'Aunty');
    $otherChild = $otherFamily->addNewChild(fake()->name(), now()->subMonth(7));

    $this->deleteJson(route('children.destroy', $otherChild))->assertForbidden();
  }
}
