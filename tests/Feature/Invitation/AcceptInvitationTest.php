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
    $this->invitation = $this->family->inviteAdult($this->manager, fake()->unique()->safeEmail(), fake()->name());

    $this->user = $this->createUser(['email' => $this->invitation->email]);
  }

  /** @test */
  public function a_guest_cannot_accept_an_invitation()
  {
    $this->postJson(route('invitations.accept', $this->invitation), ['role' => 'Mother'])->assertUnauthorized();
  }

  /** @test */
  public function a_user_cannot_accept_an_invitation_that_is_not_for_them()
  {
    $wrongUser = $this->createUser();
    $this->actingAs($wrongUser);

    $this->postJson(route('invitations.accept', $this->invitation), ['relation' => 'Mother'])->assertForbidden();
  }

  /** @test */
  public function the_managaer_of_a_families_invitation_cannot_accept_the_invitation()
  {
    $this->actingAs($this->manager);

    $this->postJson(route('invitations.accept', $this->invitation), ['relation' => 'Mother'])->assertForbidden();
  }

  /** @test */
  public function a_user_can_accept_an_invitatation_that_belongs_to_their_email()
  {
    $this->actingAs($this->user);

    $response = $this->postJson(route('invitations.accept', $this->invitation), ['relation' => 'Mother']);

    $response->assertSuccessful();

    $this->family->refresh();
    $this->assertTrue($this->family->adults->contains($this->user));
  }

  /** @test */
  public function when_a_user_accepts_an_invitation_it_deletes_the_invitation()
  {
    $this->actingAs($this->user);

    $response = $this->postJson(route('invitations.accept', $this->invitation), ['relation' => 'Mother']);

    $response->assertSuccessful();

    $this->assertDatabaseMissing('invitations', [
      'email' => $this->user->email,
    ]);
  }
}
