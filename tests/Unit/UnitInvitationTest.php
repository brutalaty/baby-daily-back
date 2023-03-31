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
  }

  /** @test */
  public function an_invitation_belongs_to_a_family()
  {
    $invitation = Invitation::factory()->create();
    $this->assertInstanceOf(Family::class, $invitation->family);
  }

  /** @test */
  public function an_invitation_has_a_status_default_of_unaccepted()
  {
    $invitation = Invitation::factory()->create()->refresh();

    $this->assertDatabaseHas('invitations', ['status' => config('invitations.status.unaccepted')]);
    $this->assertEquals(config('invitations.status.unaccepted'), $invitation->status);
  }

  /** @test */
  public function an_invitation_can_cancel()
  {
    $invitation = Invitation::factory()->create();
    $invitation->cancel();
    $this->assertEquals(config('invitations.status.canceled'), $invitation->status);
  }

  /** @test */
  public function an_invitation_has_a_function_is_canceled()
  {
    $invitation = Invitation::factory()->create();
    $invitation->cancel();
    $this->assertTrue($invitation->isCanceled());
  }

  /** @test */
  public function an_invitation_can_decline()
  {
    $invitation = Invitation::factory()->create();
    $invitation->decline();
    $this->assertEquals(config('invitations.status.declined'), $invitation->status);
  }

  /** @test */
  public function an_invitation_has_a_function_is_declined()
  {
    $invitation = Invitation::factory()->create();
    $invitation->decline();
    $this->assertTrue($invitation->isDeclined());
  }

  /** @test */
  public function an_invitation_can_accept()
  {
    $invitation = Invitation::factory()->create(['email' => $this->user->email]);
    $invitation->accept($this->user);
    $this->assertEquals(config('invitations.status.accepted'), $invitation->status);
  }

  /** @test */
  public function an_invitation_has_a_function_is_accepted()
  {
    $invitation = Invitation::factory()->create(['email' => $this->user->email]);
    $invitation->accept($this->user);
    $this->assertTrue($invitation->isAccepted());
  }
}
