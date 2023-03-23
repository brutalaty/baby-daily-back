<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\Family;
use App\Models\Child;
use App\Models\User;
use App\Models\invitation;

use Illuminate\Foundation\Testing\DatabaseMigrations;

class UnitFamilyTest extends TestCase
{

    use DatabaseMigrations;

    protected $family;

    public function setUp(): void
    {
        parent::setUp();

        $this->family = Family::factory()->create(['name' => 'test']);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }


    /** @test */
    public function a_family_can_have_many_children()
    {
        $this->family->children()->saveMany([
            Child::factory()->create(),
            Child::factory()->create()
        ]);

        $this->assertInstanceOf(Child::class, $this->family->children->first());
    }

    /** @test */
    public function a_family_can_have_many_adults()
    {
        $this->family->adults()->attach([
            User::factory()->create()->id => ['relation' => 'Father'],
            User::factory()->create()->id => ['relation' => 'Mother'],
        ]);

        $this->assertInstanceOf(User::class, $this->family->adults->first());
        $this->assertCount(2, $this->family->adults);
    }

    /** @test */
    public function a_family_has_a_function_to_attach_an_adult()
    {
        $user = $this->createUser();
        $this->family->addAdult($user, 'Father');
        $user->refresh();

        $this->assertEquals($this->family->name, $user->families->first()->name);
    }

    /** @test */
    public function a_family_has_a_function_to_create_a_child()
    {
        $this->assertCount(0, $this->family->children);
        $birthdate = $this->date_string_from_today_subtracting('2 years');
        $this->family->addNewChild('Baby Cakes', $birthdate);

        $this->family->refresh();
        $this->assertCount(1, $this->family->children);
        $this->assertEquals('Baby Cakes', $this->family->children->first()->name);
    }

    /** @test */
    public function when_a_user_creates_a_family_they_become_its_manager()
    {
        $user = $this->createUser();
        $family = $user->createFamily('Test Family', 'Test Father');

        $this->assertEquals($family->adults[0]->member->manager, 1);
    }

    /** @test */
    public function a_family_has_a_function_to_get_its_manager()
    {
        $user = $this->createUser();
        $family = $user->createFamily('Test Family', 'Test Father');

        // fwrite(STDERR, print_r($log, TRUE));

        $this->assertInstanceOf(User::class, $family->getManager());
        $this->assertEquals($family->getManager()->id, $user->id);
    }

    /** @test */
    public function a_family_has_a_function_to_invite_an_adult()
    {
        $user = $this->createUser();
        $family = $user->createFamily(fake()->lastName(), 'Father');

        $family->inviteAdult($user, 'someone@something.com', fake()->name());

        $invitation = $family->invitations()->first();

        $this->assertInstanceOf(Invitation::class, $family->invitations()->first());
        $this->assertEquals($invitation->email, 'someone@something.com');
    }
}
