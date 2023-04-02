<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Family;

use Illuminate\Support\Facades\Mail;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;

class UserAvatarTest extends TestCase
{
  use DatabaseMigrations;

  protected User $manager;
  protected Family $family;

  public function setUp(): void
  {
    parent::setUp();
    Mail::fake();
    $this->manager = $this->createUser();
    $this->family = $this->manager->createFamily(fake()->lastName(), 'Father');
  }

  /** @test */
  public function a_user_gets_a_url_to_their_avatar()
  {
    $this->actingAs($this->manager);

    $response = $this->getJson('/user');
    $response->assertJsonPath('data.avatar', fn ($avatar) => str_starts_with($avatar, 'http'));
  }
}
