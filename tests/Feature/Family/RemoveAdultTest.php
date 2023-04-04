<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Family;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;

class RemoveAdultTest extends TestCase
{
  use DatabaseMigrations;

  protected User $manager;
  protected User $adult;
  protected Family $family;

  public function setUp(): void
  {
    parent::setUp();
    $this->manager = $this->createUser();
    $this->adult = $this->createUser();
    $this->family = $this->manager->createFamily(fake()->lastName(), 'Father');

    $this->family->addAdult($this->adult, 'Uncle');

    $this->family->refresh();
  }

  /** @test */
  public function a_manager_can_remove_an_adult_from_their_family()
  {
    $this->actingAs($this->manager);
    $this->assertTrue($this->family->adults->contains($this->adult));

    $this->deleteJson(route('families.users.delete', ['family' => $this->family, 'user' => $this->adult]))
      ->assertSuccessful();
    $this->assertFalse($this->family->fresh()->adults->contains($this->adult));
  }

  /** @test */
  public function a_guest_cannot_remove_an_adult_from_a_family()
  {
    $this->deleteJson(route('families.users.delete', ['family' => $this->family, 'user' => $this->adult]))->assertUnauthorized();
  }

  /** @test */
  public function an_non_managing_adult_cannot_remove_other_adults_from_their_family()
  {
    $otherAdult = $this->createUser();
    $this->family->addAdult($otherAdult, 'Aunty');

    $this->actingAs($this->adult);

    $this->deleteJson(route('families.users.delete', ['family' => $this->family, 'user' => $otherAdult]))->assertForbidden();
  }

  /** @test */
  public function a_manager_cannot_remove_themselves_from_the_family()
  {
    $this->actingAs($this->manager);
    $this->assertTrue($this->family->adults->contains($this->manager));

    $this->deleteJson(route('families.users.delete', ['family' => $this->family, 'user' => $this->manager]))
      ->assertForbidden();
    $this->assertTrue($this->family->fresh()->adults->contains($this->manager));
  }
}
