<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Family;

use Illuminate\Foundation\Testing\DatabaseMigrations;

class UnitInvitationTest extends TestCase
{
  use DatabaseMigrations;

  private $user;


  public function setUp(): void
  {
    parent::setUp();

    $this->user = $this->createUser();
    // $family->invitations
  }

  /** @test */
  public function an_invitation_belongs_to_a_family()
  {
    $invitation = Invitation::factory()->create();
    $this->assertInstanceOf(Family::class, $invitation->family);
  }
}
