<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Family;

use App\Mail\FamilyInvitation;

use Illuminate\Support\Facades\Mail;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;

class DeclineInvitationTest extends TestCase
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
  public function a_user_can_decline_their_invitation_into_a_family()
  {
    $this->actingAs($this->user);
    $this->patchJson(route('invitations.update', $this->invitation), ['status' => config('invitations.status.declined')])->assertSuccessful();

    $this->assertTrue($this->invitation->fresh()->status == config('invitations.status.declined'));
  }

  /** @test
   * regression
   */
  public function when_a_user_declines_an_invitation_it_does_not_accept_it()
  {
    $this->actingAs($this->user);
    $this->patchJson(route('invitations.update', $this->invitation), ['status' => config('invitations.status.declined')])->assertSuccessful();

    $this->assertFalse($this->family->fresh()->adults->contains($this->user));
  }

  /** @test */
  public function a_manager_cannot_decline_an_invitation_to_their_family()
  {
    $this->actingAs($this->manager);
    $this->patchJson(route('invitations.update', $this->invitation), ['status' => config('invitations.status.declined')])->assertForbidden();
  }


  /** @test */
  public function a_guest_cannot_decline_an_invitation()
  {
    $this->patchJson(route('invitations.update', $this->invitation), ['status' => config('invitations.status.declined')])->assertUnauthorized();
  }

  /** @test */
  public function a_user_cannot_decline_an_invitation_that_is_not_unaccepted_status()
  {
    $this->actingAs($this->user);

    $canceled = $this->createInvitation(config('invitations.status.canceled'));
    $declined = $this->createInvitation(config('invitations.status.declined'));
    $accepted = $this->createInvitation(config('invitations.status.accepted'));

    $this->patchJson(route('invitations.update', $canceled), ['status' => config('invitations.status.declined')])->assertForbidden();
    $this->patchJson(route('invitations.update', $declined), ['status' => config('invitations.status.declined')])->assertForbidden();
    $this->patchJson(route('invitations.update', $accepted), ['status' => config('invitations.status.declined')])->assertForbidden();
  }

  private function createInvitation(String $status)
  {
    $invitation =  $this->family->inviteAdult($this->manager, $this->user->email, fake()->name(), 'Uncle');
    $invitation->status = $status;
    $invitation->save();
    return $invitation;
  }
}
