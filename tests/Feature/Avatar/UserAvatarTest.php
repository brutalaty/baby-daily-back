<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Family;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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

  /** @test */
  public function when_getting_a_family_the_adults_have_avatars()
  {
    $this->actingAs($this->manager);

    $response = $this->getJson(route('families.index'));
    $response->assertJsonPath('data.0.adults.0.avatar', fn ($avatar) => str_starts_with($avatar, 'http'));
  }

  /** @test */
  public function a_user_can_change_their_avatar()
  {
    $user = $this->createUser();
    $this->actingAs($user);

    Storage::disk('users')->assertExists($user->avatar);
    Storage::disk('users')->delete($user->avatar);
    Storage::disk('users')->assertMissing($user->avatar);

    $file = UploadedFile::fake()->image('avatar.png');

    $this->patchJson(route('user.avatar'), ['avatar' => $file])->assertSuccessful();

    Storage::disk('users')->assertExists($user->avatar);
  }

  /** @test */
  public function a_users_avatar_is_limited_in_size()
  {
    $this->actingAs($this->manager);

    Storage::fake('users');

    $file = UploadedFile::fake()->image('avatar.png')->size(1000);

    $this->patchJson(route('user.avatar'), ['avatar' => $file])->assertUnprocessable();
  }

  /** @test */
  public function updating_a_users_avatar_will_remove_the_old_avatar_if_they_have_differing_extensions()
  {
    $this->actingAs($this->manager);

    $oldFile = $this->manager->avatar;
    $file = UploadedFile::fake()->image('avatar.jpg');

    $this->patchJson(route('user.avatar'), ['avatar' => $file])->assertSuccessful();

    Storage::disk('users')->assertExists($this->manager->fresh()->avatar);
    Storage::disk('users')->assertMissing($oldFile);
  }
}
