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
}
