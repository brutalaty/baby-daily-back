<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Family;
use App\Models\Child;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;

class ChildAvatarTest extends TestCase
{
  use DatabaseMigrations;

  protected User $manager;
  protected Family $family;
  protected Child $child;

  public function setUp(): void
  {
    parent::setUp();
    Mail::fake();
    $this->manager = $this->createUser();
    $this->family = $this->manager->createFamily(fake()->lastName(), 'Father');
    $this->child = $this->family->addNewChild(fake()->name(), now()->subYear());
  }

  /** @test */
  public function getting_a_child_returns_with_an_avatar_url()
  {
    $this->actingAs($this->manager);

    $response = $this->getJson(route('children.show', $this->child));
    $response->assertJsonPath('data.avatar', fn ($avatar) => str_starts_with($avatar, 'http'));
  }

  /** @test */
  public function when_getting_a_family_the_children_have_avatars()
  {
    $this->actingAs($this->manager);

    $response = $this->getJson(route('families.index'));
    $response->assertJsonPath('data.0.children.0.avatar', fn ($avatar) => str_starts_with($avatar, 'http'));
  }

  /** @test */
  public function the_manager_of_the_family_can_change_its_childrens_avatars()
  {
    Storage::fake('children');
    $this->actingAs($this->manager);

    $child = $this->family->addNewChild(fake()->name(), now()->subYear());
    $child->refresh();


    Storage::disk('children')->delete($child->avatar);
    Storage::disk('children')->assertMissing($child->fresh()->avatar);

    $file = UploadedFile::fake()->image('avatar.png');

    $this->patchJson(
      route('children.avatar', $child),
      ['avatar' => $file]
    )->assertSuccessful();
    Storage::disk('children')->assertExists($child->fresh()->avatar);
  }

  /** @test */
  public function a_non_managing_user_cannot_change_a_childs_avatar()
  {
    $user = $this->createUser();
    $this->family->addAdult($user, 'Grand Mother');

    $this->actingAs($user);

    $file = UploadedFile::fake()->image('avatar.png');

    $this->patchJson(
      route('children.avatar', $this->child),
      ['avatar' => $file]
    )->assertForbidden();
  }

  /** @test */
  public function an_avatar_must_be_an_image_file()
  {
    $this->actingAs($this->manager);

    $file = UploadedFile::fake()->create('document.pdf', '2');

    $this->patchJson(
      route('children.avatar', $this->child),
      ['avatar' => $file]
    )->assertUnprocessable();
  }


  /** @test */
  public function an_avatar_must_be_under_limited_in_file_size()
  {
    $this->actingAs($this->manager);

    $file = UploadedFile::fake()->image('avatar.png')->size(1000);

    $this->patchJson(
      route('children.avatar', $this->child),
      ['avatar' => $file]
    )->assertUnprocessable();
  }

  /** @test */
  public function an_old_avatar_that_has_a_different_extension_gets_removed()
  {
    $this->actingAs($this->manager);

    $child = $this->family->addNewChild(fake()->name(), now()->subYear());
    $child->refresh();

    $oldFile = $child->avatar;
    $file = UploadedFile::fake()->image('avatar.jpg');

    $this->patchJson(
      route('children.avatar', $child),
      ['avatar' => $file]
    )->assertSuccessful();

    Storage::disk('children')->assertExists($child->fresh()->avatar);
    Storage::disk('children')->assertMissing($oldFile);
  }
}
