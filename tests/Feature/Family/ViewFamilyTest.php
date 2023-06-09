<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Family;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\Fluent\AssertableJson;

use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ViewFamilyTest extends TestCase
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
        $this->family = Family::factory()->create();
        $this->family->addAdult($this->user, 'Father');
    }

    /** @test */
    public function guests_cannot_a_get_families()
    {
        $this->getJson(route('families.index'))->assertUnauthorized();

        $this->getJson(route('families.show', $this->family))->assertUnauthorized();
    }

    /** @test */
    public function users_do_not_get_families_that_they_dont_belong_to()
    {
        $lonelyAdult = User::factory()->create();
        $this->actingAs($lonelyAdult);

        Family::factory()->count(3)->create();

        $this->getJson(route('families.index'))
            ->assertJson(fn ($json) => $json->has('data', 0));
    }

    /** @test */
    public function users_cannot_get_a_family_they_do_not_belong_to()
    {
        $this->actingAs($this->user);
        $families = Family::factory()->count(3)->create();

        $this->getJson(route('families.show', $families->first()))
            ->assertForbidden();
    }

    /** @test */
    public function users_can_get_the_families_that_they_belong_to()
    {
        $this->actingAs($this->user);
        //we assume the setUp() method has given this user one family already
        $families = Family::factory()->count(3)->create();
        $family = $families->last();
        $family->addAdult($this->user, 'Father');

        $this->getJson(route('families.index'))->assertJson(
            fn (AssertableJson $json) =>
            $json->has('data', 2)
                ->has(
                    'data.0',
                    fn ($json) =>
                    $json->where('name', $this->family->name)
                        ->etc()
                )
                ->has('data.1', fn ($json) =>
                $json->where('name', $family->name)
                    ->etc())
        );
    }

    /** @test */
    public function users_can_get_a_family_that_they_belong_to()
    {
        $this->actingAs($this->user);

        $this->getJson(route('families.show', $this->family))->assertJsonPath(
            'data.name',
            $this->family->name
        );
    }

    /** @test */
    public function families_also_have_their_invitations_attached()
    {
        Mail::fake();
        $user = $this->createUser();
        $this->actingAs($user);
        $family = $user->createFamily(fake()->lastName(), 'father');

        $invitation = $family->inviteAdult($user, fake()->email(), fake()->name(), 'Uncle');

        $this->getJson(route('families.show', $family))->assertJsonPath('data.invitations.0.name', $invitation->name);

        $this->getJson(route('families.index'))->assertJsonPath('data.0.invitations.0.name', $invitation->name);
    }

    /** @test */
    public function when_getting_families_the_adults_have_a_family_id()
    {
        $this->actingAs($this->user)
            ->getJson(route('families.index'))
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->has(
                    'data.0.adults.0',
                    fn ($json) =>
                    $json->where('family_id', $this->family->id)
                        ->etc()
                )
            );
    }
}
