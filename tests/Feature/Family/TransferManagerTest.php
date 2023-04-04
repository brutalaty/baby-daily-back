<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Family;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;

class TransferManagerTest extends TestCase
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
  public function a_manager_can_transfer_the_manager_status_to_another_adult_within_the_family()
  {
    $this->actingAs($this->manager);
    $this->assertTrue($this->family->isManager($this->manager));

    $this->patchJson(route('families.users.manager', ['family' => $this->family, 'user' => $this->adult]))->assertSuccessful();

    $this->family->refresh();

    $this->assertFalse($this->family->isManager($this->manager));
    $this->assertTrue($this->family->isManager($this->adult));
  }

  /** @test */
  public function a_guest_cannot_transfer_manager_status()
  {
    $this->patchJson(route('families.users.manager', ['family' => $this->family, 'user' => $this->adult]))->assertUnauthorized();
  }

  /** @test */
  public function a_non_managing_adult_cannot_transfer_manager_status()
  {
    $this->actingAs($this->adult);
    $otherAdult = $this->createUser();
    $this->family->addAdult($otherAdult, 'Aunty');

    $this->patchJson(route('families.users.manager', ['family' => $this->family, 'user' => $otherAdult]))->assertForbidden();
  }

  /** @test */
  public function when_transfering_manager_status_the_target_adult_cannot_be_outside_the_family()
  {
    $this->actingAs($this->manager);
    $this->assertTrue($this->family->isManager($this->manager));

    $otherAdult = $this->createUser();

    $this->patchJson(route('families.users.manager', ['family' => $this->family, 'user' => $otherAdult]))->assertForbidden();
  }
}
