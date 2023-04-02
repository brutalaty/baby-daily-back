<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\Child;
use App\Models\Family;
use App\Models\Poop;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UnitChildTest extends TestCase
{
    use DatabaseMigrations;

    private $child;


    public function setUp(): void
    {
        parent::setUp();

        $this->child = Child::factory()->create(['name' => 'test']);
    }

    /** @test */
    public function a_child_blongs_to_a_family()
    {
        $this->assertInstanceOf(Family::class, $this->child->family);
    }


    /** @test */
    public function children_have_an_avatar()
    {
        $expected = config('avatars.path.children.db')
            . $this->child->id
            . config('avatars.file.type');

        $this->assertNotNull($this->child->avatar);
        $this->assertEquals($expected, $this->child->avatar);
        $this->assertFileExists(config('avatars.path.children.testprefix') . $this->child->avatar);
    }

    /** @test */
    public function when_a_child_is_deleted_their_avatar_is_deleted()
    {
        $location = config('avatars.path.children.testprefix') . $this->child->avatar;
        $this->assertFileExists($location);
        $this->child->delete();
        $this->assertFileExists($location);
    }

    /** @test */
    public function a_child_has_a_function_to_get_the_avatars_web_url()
    {
        $this->assertStringContainsString('http', $this->child->avatarUrl());
        $this->assertStringContainsString($this->child->avatar, $this->child->avatarUrl());
    }
}
