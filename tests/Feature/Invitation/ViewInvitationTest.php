<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Family;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\Fluent\AssertableJson;

use Tests\TestCase;


class ViewInvitationTest extends TestCase
{
  use DatabaseMigrations;

  protected User $manager;
  protected Family $family;
  protected Invitation $invitation;
  protected String $relation = 'Aunty';


  public function setUp(): void
  {
    parent::setUp();
    Mail::fake();
    $this->manager = $this->createUser();
    $this->family = $this->manager->createFamily(fake()->lastName(), 'Father');
    $this->invitation = $this->family->inviteAdult($this->manager, fake()->email(), fake()->name(), $this->relation);
  }

  /** @test */
  public function there_is_no_invitation_get_by_id_route()
  {
    $this->actingAs($this->manager);

    $this->assertNotTrue(Route::getRoutes()->hasNamedRoute('families.invitations.show'));
    $this->assertNotTrue(Route::getRoutes()->hasNamedRoute('invitations.show'));

    $this->getJson("/families/{$this->family->id}/invitations/{$this->invitation->id}")->assertNotFound();
    $this->getJson("/invitations/{$this->invitation->id}")->assertStatus(405); //method not allowed
  }

  /** @test */
  public function a_guest_cannot_view_invitations()
  {
    $this->getJson(route('invitations.index', $this->family))->assertUnauthorized();
  }

  /** @test */
  public function a_user_can_get_its_invitations()
  {
    $this->actingAs($this->createUser(['email' => $this->invitation->email]));

    $response = $this->getJson(route('invitations.index'));

    $response->assertJson(
      fn (AssertableJson $json) =>
      $json->has('data')
        ->has('data', 1)
        ->has(
          'data.0',
          fn (AssertableJson $json) =>
          $json->where('name', $this->invitation->name)
            ->where('family_name', $this->family->name)
            ->where('id', $this->invitation->id)
            ->where('status', config('invitations.status.unaccepted'))
            ->where('relation', $this->relation)
            ->etc()
        )
    );
  }

  /** @test */
  public function a_user_only_gets_its_own_invitations()
  {
    $this->addInvitationsToFamily($this->family, 2);

    $user = $this->createUser(['email' => $this->invitation->email]);
    $this->actingAs($user);

    $response = $this->getJson(route('invitations.index'));

    $response->assertJson(fn (AssertableJson $json) =>
    $json->has('data')
      ->has('data', 1));
  }

  private function addInvitationsToFamily(Family $family, $count)
  {
    for ($i = 0; $i < $count; $i++) {
      $family->inviteAdult($this->manager, fake()->unique()->email(), fake()->name(), $this->relation);
    }
  }
}
