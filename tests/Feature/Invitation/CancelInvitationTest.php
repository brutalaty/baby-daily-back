<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Family;

use App\Mail\FamilyInvitation;

use Illuminate\Support\Facades\Mail;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;

class CancelInvitationTest extends TestCase
{
  use DatabaseMigrations;

  protected User $manager;
  protected User $user;
  protected Family $family;
  protected Invitation $invitation;

  public function setUp(): void
  {
    parent::setUp();
    Mail::fake();
    $this->manager = $this->createUser();
    $this->family = $this->manager->createFamily(fake()->lastName(), 'Father');
    $this->invitation = $this->family->inviteAdult($this->manager, fake()->email(), fake()->name(), 'Grandfather');
    $this->user = $this->createUser(['email' => $this->invitation->email]);
  }

  /** @test */
  public function a_manager_of_the_family_can_cancel_an_invitation()
  {
    $this->actingAs($this->manager);

    $response = $this->patchJson(route('invitations.update', $this->invitation), ['status' => config('invitations.status.canceled')]);

    $response->assertSuccessful();
    $this->assertTrue($this->invitation->fresh()->status == config('invitations.status.canceled'));
  }

  /** @test */
  public function a_user_that_is_not_a_manager_cannot_cancel_an_invitation()
  {
    $this->actingAs($this->createUser());

    $this->patchJson(route('invitations.update', $this->invitation), ['status' => config('invitations.status.canceled')])->assertForbidden();
  }

  /** @test */
  public function the_invited_user_cannot_cancel_an_invitation()
  {
    $this->actingAs($this->user);

    $this->patchJson(route('invitations.update', $this->invitation), ['status' => config('invitations.status.canceled')])->assertForbidden();
  }

  /** @test */
  public function guests_cannot_cancel_an_invitation()
  {
    $this->patchJson(route('invitations.update', $this->invitation), ['status' => config('invitations.status.canceled')])->assertUnauthorized();
  }

  /** @test */
  public function an_expired_invitation_cannot_be_canceled()
  {
    $this->actingAs($this->manager);
    $this->invitation->expiration = now()->subMonth();
    $this->invitation->save();

    $this->patchJson(route('invitations.update', $this->invitation), ['status' => config('invitations.status.canceled')])->assertForbidden();
  }

  /** @test */
  public function a_manager_cannot_cancel_an_invitation_that_is_not_unaccepted_status()
  {
    $this->actingAs($this->manager);

    $canceled = $this->createInvitation(config('invitations.status.canceled'));
    $declined = $this->createInvitation(config('invitations.status.declined'));
    $accepted = $this->createInvitation(config('invitations.status.accepted'));

    $this->patchJson(route('invitations.update', $canceled), ['status' => config('invitations.status.canceled')])->assertForbidden();
    $this->patchJson(route('invitations.update', $declined), ['status' => config('invitations.status.canceled')])->assertForbidden();
    $this->patchJson(route('invitations.update', $accepted), ['status' => config('invitations.status.canceled')])->assertForbidden();
  }


  private function createInvitation(String $status)
  {
    $invitation =  $this->family->inviteAdult($this->manager, $this->user->email, fake()->name(), 'Uncle');
    $invitation->status = $status;
    $invitation->save();
    return $invitation;
  }
}
