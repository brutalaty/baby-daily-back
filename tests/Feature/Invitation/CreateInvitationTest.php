<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Family;

use App\Mail\FamilyInvitation;

use Illuminate\Support\Facades\Mail;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;

use \DateTime;

class CreateInvitationTest extends TestCase
{
  use DatabaseMigrations;

  protected $user;

  public function setUp(): void
  {
    parent::setUp();
    Mail::fake();
    $this->user = $this->createUser();
  }

  /** @test */
  public function an_invitation_requires_relation()
  {
    $this->actingAs($this->user);
    $family = $this->user->createFamily(fake()->lastName(), 'Mother');

    $response = $this->postJson(route('families.invitations.store', $family));

    $response->assertJsonValidationErrorFor('relation');
  }

  /** @test */
  public function an_invitation_requires_an_email()
  {
    $this->actingAs($this->user);
    $family = $this->user->createFamily(fake()->lastName(), 'Mother');

    $response = $this->postJson(route('families.invitations.store', $family));

    $response->assertJsonValidationErrorFor('email');
  }

  /** @test */
  public function an_invitation_requires_a_name()
  {
    $this->actingAs($this->user);
    $family = $this->user->createFamily(fake()->lastName(), 'Mother');

    $response = $this->postJson(route('families.invitations.store', $family));

    $response->assertJsonValidationErrorFor('name');
  }

  /** @test */
  public function a_guest_cannot_invite_to_a_family()
  {
    $family = Family::factory()->create();

    $response = $this->postJson(route('families.invitations.store', $family));

    $response->assertUnauthorized();
  }

  /** @test */
  public function users_who_are_not_a_family_member_cannot_invite_to_that_family()
  {
    $family = Family::factory()->hasAttached(
      User::factory(),
      ['relation' => 'father', 'manager' => true],
      'Adults'
    )->create();

    $this->actingAs($this->createUser());

    $response = $this->postJson(route('families.invitations.store', $family), [
      'name' => 'test',
      'relation' => 'Uncle',
      'email' => fake()->email()
    ]);

    $response->assertForbidden();
  }

  /** @test */
  public function users_who_are_not_managers_of_a_family_cannot_invite_to_that_family()
  {
    $otherUser = $this->createUser();
    $family = $otherUser->createFamily(fake()->lastName(), 'father');
    $family->addAdult($this->user, 'Mother');

    $this->actingAs($this->user);

    $response = $this->postJson(route('families.invitations.store', $family), [
      'name' => 'test',
      'relation' => 'Uncle',
      'email' => fake()->email()
    ]);

    $response->assertForbidden();
  }

  /** @test */
  public function a_manager_of_a_family_cannot_invite_to_another_family_that_they_are_not_a_member_of()
  {
    $this->actingAs($this->user);
    $this->user->createFamily(fake()->lastName(), 'father');

    $otherUser = $this->createUser();
    $otherFamily = $otherUser->createFamily(fake()->lastName(), 'father');

    $this->postJson(route('families.invitations.store', $otherFamily), [
      'name' => fake()->name(),
      'relation' => 'Uncle',
      'email' => fake()->email()
    ])
      ->assertForbidden();
  }

  /** @test */
  public function a_manager_of_a_family_cannot_invite_to_another_family_that_they_are_not_a_managing_member()
  {
    $this->actingAs($this->user);
    $this->user->createFamily(fake()->lastName(), 'father');

    $otherManager = $this->createUser();
    $otherFamily = $otherManager->createFamily(fake()->lastName(), 'father');

    $otherFamily->addAdult($this->user, 'Mother');

    $response = $this->postJson(route('families.invitations.store', $otherFamily), [
      'name' => fake()->name(),
      'relation' => 'Uncle',
      'email' => fake()->email()
    ]);
    $response->assertForbidden();
  }

  /** @test */
  public function a_member_of_a_family_cannot_be_invited_to_that_family_again()
  {
    $this->actingAs($this->user);
    $family = $this->user->createFamily(fake()->name(), 'Father');
    $existingMember = $this->createUser();
    $family->adults()->attach($existingMember, ['relation' => 'Mother']);
    $this->assertCount(2, $family->adults);

    $response = $this->postJson(
      route('families.invitations.store', $family),
      [
        'name' => $existingMember->name,
        'relation' => 'Uncle',
        'email' => $existingMember->email
      ]
    );

    $response->assertUnprocessable();
  }

  /** @test */
  public function a_families_manager_can_create_an_invite_to_that_family()
  {
    $family = $this->user->createFamily(fake()->lastName(), 'father');
    $name = fake()->name();
    $email = fake()->email();
    $relation = 'Uncle';
    $this->actingAs($this->user);

    $response = $this->postJson(route('families.invitations.store', $family), [
      'name' => $name,
      'relation' => $relation,
      'email' => $email
    ]);

    $response->assertSuccessful();

    $response->assertJsonPath('data.name', $name);
    $response->assertJsonPath('data.relation', $relation);
    $response->assertJsonPath('data.family_name', $family->name);
  }

  /** @test */
  public function a_new_invitation_has_the_status_of_unaccepted()
  {
    $this->withoutExceptionHandling();
    $family = $this->user->createFamily(fake()->lastName(), 'father');
    $this->actingAs($this->user);

    $response = $this->postJson(route('families.invitations.store', $family), [
      'name' => fake()->name(),
      'relation' => 'uncle',
      'email' => fake()->email()
    ]);

    $response->assertJsonPath('data.status', config('invitations.status.unaccepted'));
  }

  /** @test */
  public function a_new_invitation_has_an_expiration_that_is_set_to_a_future_time()
  {
    $family = $this->user->createFamily(fake()->lastName(), 'father');
    $this->actingAs($this->user);

    $response = $this->postJson(route('families.invitations.store', $family), [
      'name' => fake()->name(),
      'relation' => 'uncle',
      'email' => fake()->email()
    ]);

    $response->assertJsonPath('data.expiration', function (string $expiration) {
      $date = new DateTime($expiration);
      return $date > now();
    });
  }

  /** @test 
   * live invitation: an unexpired and status:unaccepted invitation.
   */
  public function when_creating_an_invite_it_will_cancel_any_live_invitations_to_that_email()
  {
    $this->actingAs($this->user);
    $family = $this->user->createFamily(fake()->lastName(), 'Father');
    $invitation = $family->inviteAdult($this->user, fake()->email(), fake()->name(), 'Uncle');

    $this->assertEquals(config('invitations.status.unaccepted'), $invitation->status);

    $response = $this->postJson(
      route('families.invitations.store', $family),
      ['name' => fake()->name(), 'relation' => 'uncle', 'email' => $invitation->email]
    );

    $response->assertSuccessful();

    $this->assertEquals(config('invitations.status.canceled'), $invitation->refresh()->status);
  }

  /** @test 
   * live invitation: an unexpired and status:unaccepted invitation.
   */
  public function when_creating_an_invite_it_wont_cancel_inactive_invitations_to_that_email()
  {
    $this->actingAs($this->user);
    $family = $this->user->createFamily(fake()->lastName(), 'Father');
    $email = fake()->email();
    $name = fake()->name();
    $relation = 'Uncle';

    $invitationExpired = $this->createInvitationExpired($family, $this->user, $email, $name, $relation);
    $invitationDeclined = $this->createInvitationWithStatus($family, $this->user, $email, $name, $relation, config('invitations.status.declined'));
    $invitationAccepted = $this->createInvitationWithStatus($family, $this->user, $email, $name, $relation, config('invitations.status.accepted'));

    $this->postJson(
      route('families.invitations.store', $family),
      ['name' => $name, 'relation' => $relation, 'email' => $email]
    )->assertSuccessful();

    $this->assertEquals(config('invitations.status.unaccepted'), $invitationExpired->status);
    $this->assertEquals(config('invitations.status.declined'), $invitationDeclined->status);
    $this->assertEquals(config('invitations.status.accepted'), $invitationAccepted->status);
  }

  private function createInvitationWithStatus(Family $family, User $user, String $email, String $name, String $relation, $status): Invitation
  {
    $invitation = $family->inviteAdult($user, $email, $name, $relation);
    $invitation->status = $status;
    $invitation->save();
    return $invitation;
  }

  private function createInvitationExpired(Family $family, User $user, String $email, String $name, String $relation): Invitation
  {
    $invitation = $family->inviteAdult($user, $email, $name, $relation);
    $invitation->expiration = now()->subMonth();
    $invitation->save();
    return $invitation;
  }


  /** @test */
  public function invitation_email_shows_correct_content()
  {
    $family = $this->user->createFamily(fake()->lastName(), 'father');
    $emailAddress = fake()->email();
    $name = fake()->name();
    //avoid triggering emailing event by manually creating the invitation. The event to email is triggered by using the Family->inviteAdult method
    $invitation = new Invitation();
    $invitation->email = $emailAddress;
    $invitation->name = $name;
    $invitation->relation = 'uncle';
    $invitation->family_id = $family->id;
    $invitation->expiration = now();
    $invitation->save();

    $mail = new FamilyInvitation($this->user, $invitation);

    $mail->assertTo($emailAddress, $name);
    $mail->assertFrom(config('mail.from.address'));
    $mail->assertSeeInHtml($name);
    $mail->assertSeeInHtml($this->user->name);
    $mail->assertSeeInHtml($family->name);
  }

  /** @test */
  public function when_an_invitation_is_created_an_invitation_email_is_sent()
  {
    $this->actingAs($this->user);
    $family = $this->user->createFamily(fake()->lastName(), 'Father');
    $email = fake()->email();
    $name = fake()->name();

    $response = $this->postJson(route('families.invitations.store', $family), ['email' => $email, 'name' => $name, 'relation' => 'uncle']);
    $response->assertSuccessful();

    Mail::assertSent(FamilyInvitation::class);
  }
}
