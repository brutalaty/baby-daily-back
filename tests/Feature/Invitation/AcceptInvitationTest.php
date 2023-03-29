<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Family;

use Illuminate\Support\Facades\Mail;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;

class AcceptInvitationTest extends TestCase
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
    $this->invitation = $this->family->inviteAdult($this->manager, fake()->unique()->safeEmail(), fake()->name(), 'Uncle');

    $this->user = $this->createUser(['email' => $this->invitation->email]);
  }

  /** @test */
  public function a_guest_cannot_accept_an_invitation()
  {
    $this->patchJson(route('invitations.update', $this->invitation), [
      'status' => config('invitations.status.accepted')
    ])->assertUnauthorized();
  }

  /** @test */
  public function a_user_cannot_accept_an_invitation_that_is_not_for_them()
  {
    $this->actingAs($this->createUser());

    $this->patchJson(route('invitations.update', $this->invitation), [
      'status' => config('invitations.status.accepted')
    ])->assertForbidden();
  }

  /** @test */
  public function the_manager_of_a_families_invitation_cannot_accept_the_invitation()
  {
    $this->actingAs($this->manager);

    $this->patchJson(route('invitations.update', $this->invitation), [
      'status' => config('invitations.status.accepted')
    ])->assertForbidden();
  }

  /** @test */
  public function a_user_can_accept_an_invitatation_that_belongs_to_their_email()
  {
    $this->actingAs($this->user);

    $response = $this->patchJson(route('invitations.update', $this->invitation), ['status' => config('invitations.status.accepted')]);

    $response->assertSuccessful();

    $this->assertTrue($this->family->fresh()->adults->contains($this->user));
    $this->assertTrue($this->invitation->fresh()->status == config('invitations.status.accepted'));
  }

  /** @test */
  public function a_user_cannot_accept_an_expired_invitation()
  {
    $this->actingAs($this->user);
    $this->invitation->status = config('invitations.status.unaccepted');
    $this->invitation->expiration = now()->subMonth();
    $this->invitation->save();

    $this->patchJson(route('invitations.update', $this->invitation), ['status' => config('invitations.status.accepted')])->assertForbidden();
  }

  /** @test */
  public function a_user_cannot_accept_an_invitation_that_does_not_have_the_status_unaccepted()
  {
    $this->actingAs($this->user);

    $canceled = $this->createInvitation(config('invitations.status.canceled'));
    $declined = $this->createInvitation(config('invitations.status.declined'));
    $accepted = $this->createInvitation(config('invitations.status.accepted'));

    $this->patchJson(route('invitations.update', $canceled), ['status' => config('invitations.status.accepted')])->assertForbidden();
    $this->patchJson(route('invitations.update', $declined), ['status' => config('invitations.status.accepted')])->assertForbidden();
    $this->patchJson(route('invitations.update', $accepted), ['status' => config('invitations.status.accepted')])->assertForbidden();
  }

  private function createInvitation(String $status)
  {
    $invitation =  $this->family->inviteAdult($this->manager, $this->user->email, fake()->name(), 'Uncle');
    $invitation->status = $status;
    $invitation->save();
    return $invitation;
  }
}
