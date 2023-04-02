<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\User;
use App\Models\Family;
use App\Models\Child;

use Illuminate\Foundation\Testing\DatabaseMigrations;

class UnitUserTest extends TestCase
{
    use DatabaseMigrations;

    private $user;
    private $usersFamily;
    private $usersChild;
    private $notUsersChild;


    public function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
        $this->usersChild = Child::factory()->create(['name' => 'users child']);
        $this->notUsersChild = Child::factory()->create(['name' => 'not users child']);
        $this->usersFamily = Family::factory()->create(['name' => 'users family']);
    }

    /** @test */
    public function a_user_has_a_function_to_create_a_family()
    {
        $user = $this->createUser();
        $this->assertCount(0, $user->families);

        $user->createFamily('Test Family', 'Test Father');
        $user = $user->fresh();
        $this->assertCount(1, $user->families);
        $this->assertEquals('Test Family', $user->families->first()->name);
    }

    /** @test */
    public function users_have_an_avatar()
    {
        $expected = config('avatars.path.users.db')
            . $this->user->id
            . config('avatars.file.type');

        $this->assertNotNull($this->user->avatar);
        $this->assertEquals($expected, $this->user->avatar);
        $this->assertFileExists(config('avatars.path.users.testprefix') . $this->user->avatar);
    }

    /** @test */
    public function when_a_user_is_force_deleted_their_avatar_is_deleted()
    {
        $location = config('avatars.path.users.testprefix') . $this->user->avatar;
        $this->assertFileExists($location);
        $this->user->delete();
        $this->assertFileExists($location);
    }

    /** @test */
    public function a_user_has_a_function_to_get_the_avatars_web_url()
    {
        $this->assertStringContainsString('http', $this->user->avatarUrl());
        $this->assertStringContainsString($this->user->avatar, $this->user->avatarUrl());
    }
}
