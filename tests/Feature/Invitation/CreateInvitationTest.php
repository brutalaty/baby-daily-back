<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Family;

use App\Mail\FamilyInvitation;

use Illuminate\Support\Facades\Mail;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;

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
  public function a_guest_cannot_invite_to_a_family()
  {
    $family = Family::factory()->create();
    $response = $this->postJson(route('families.invitations.store', $family), ['name' => 'test', 'email' => fake()->email()]);

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

    $response = $this->postJson(route('families.invitations.store', $family), ['name' => 'test', 'email' => fake()->email()]);

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
      'email' => fake()->email()
    ]);

    $response->assertForbidden();
  }

  /** @test */
  public function a_manager_of_a_family_cannot_invite_to_a_family_they_are_not_a_manager_of()
  {
    $otherUser = $this->createUser();
    $otherFamily = $otherUser->createFamily(fake()->lastName(), 'father');

    $this->user->createFamily(fake()->lastName(), 'father');
    $this->actingAs($this->user);

    $response = $this->postJson(route('families.invitations.store', $otherFamily), [
      'name' => fake()->name(),
      'email' => fake()->email()
    ]);
    $response->assertForbidden();

    $otherFamily->addAdult($this->user, 'Mother');

    $response = $this->postJson(route('families.invitations.store', $otherFamily), [
      'name' => fake()->name(),
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
    $this->actingAs($this->user);
    // fwrite(STDERR, print_r(fake()->email(), TRUE));

    $response = $this->postJson(route('families.invitations.store', $family), [
      'name' => $name,
      'email' => $email
    ]);

    $response->assertSuccessful();

    $response->assertJsonPath('data.name', $name);
    $response->assertJsonPath('data.family_name', $family->name);
  }

  /** @test */
  public function when_attempting_to_create_an_existing_invitation_it_does_not_create_a_duplicate()
  {
    //when same family && email address is used, update rather then create
    $this->actingAs($this->user);
    $family = $this->user->createFamily(fake()->lastName(), 'father');
    $email = fake()->email();
    $data1 = ['name' => 'bob', 'email' => $email];
    $data2 = ['name' => 'brad', 'email' => $email];

    $response = $this->postJson(route('families.invitations.store', $family), $data1);

    $response->assertSuccessful();

    $response = $this->postJson(route('families.invitations.store', $family), $data2);
    $response->assertSuccessful();
    $response->assertJsonPath('data.family_name', $family->name);
    $response->assertJsonPath('data.name', $data2['name']);
    $this->assertDatabaseCount('invitations', 1);
    $this->assertDatabaseMissing('invitations', ['name' => $data1['name']]);
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

    $response = $this->postJson(route('families.invitations.store', $family), ['email' => $email, 'name' => $name]);
    $response->assertSuccessful();

    Mail::assertSent(FamilyInvitation::class);
  }

  /** @test */
  public function when_an_existing_is_updated_it_sends_another_invitation_email()
  {
    $this->actingAs($this->user);
    $family = $this->user->createFamily(fake()->lastName(), 'Father');
    $email = fake()->email();
    $name = fake()->name();

    $response = $this->postJson(route('families.invitations.store', $family), ['email' => $email, 'name' => $name]);
    $response2 = $this->postJson(route('families.invitations.store', $family), ['email' => $email, 'name' => $name]);

    $response->assertSuccessful();
    $response2->assertSuccessful();

    Mail::assertSent(FamilyInvitation::class, 2);
  }
}
