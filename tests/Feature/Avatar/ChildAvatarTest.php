<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Family;
use App\Models\Child;

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
}
