<?php

namespace Tests\Feature\Child;

use App\Models\User;
use App\Models\Family;
use App\Models\Child;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Route;

use Tests\TestCase;

class ViewChildTest extends TestCase
{
    use DatabaseMigrations;

    protected $user;
    protected $family;
    protected $families;
    protected $child;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
        $this->family = $this->user->createFamily(fake()->lastName(), "Father");
        $this->child = $this->family->addNewChild(fake()->name(), now()->subYear(2));
    }

    /** @test */
    public function route_to_getting_all_children_is_disabled()
    {

        $this->actingAs($this->user);
        $this->getJson('/children')->assertNotFound();
        $this->getJson('/families/{1}/children')->assertStatus(405);

        $this->assertFalse(Route::has('families.children'));
        $this->assertFalse(Route::has('children'));
    }

    /** @test */
    public function guests_cannot_get_a_child()
    {
        $lonelyAdult = $this->createUser();
        $this->actingAs($lonelyAdult);

        $response = $this->getJson(route('children.show', $this->child));

        $response->assertForbidden();
    }

    /** @test */
    public function a_user_cannot_get_a_child_that_is_not_a_relation()
    {
        $this->actingAs($this->user);
        $unrelatedChild = Child::factory()->create();

        $response = $this->getJson(route('children.show', $unrelatedChild));

        $response->assertForbidden();
    }


    /** @test */
    public function a_user_can_get_a_child_that_they_are_related_to()
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('children.show', $this->child));

        $response->assertJsonPath('data.name', $this->child->name);
    }

    /** @test */
    public function a_child_is_returned_with_their_age()
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('children.show', $this->child));

        $response->assertJsonPath('data.age', $this->child->age());
    }
}
