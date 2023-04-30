<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\Activity;
use App\Models\Child;
use App\Models\Family;

use Illuminate\Support\Facades\Storage;

use Illuminate\Foundation\Testing\DatabaseMigrations;

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
        $expected = $this->child->id . config('avatars.file.type');

        $this->assertNotNull($this->child->avatar);
        $this->assertEquals($expected, $this->child->avatar);
        Storage::disk('children')->assertExists($this->child->avatar);
    }

    /** @test */
    public function when_a_child_is_deleted_their_avatar_is_deleted()
    {
        $location = config('avatars.path.children.testprefix') . $this->child->avatar;
        Storage::disk('children')->assertExists($this->child->avatar);
        $this->child->delete();
        Storage::disk('children')->assertMissing($this->child->avatar);
    }

    /** @test */
    public function a_child_has_a_function_to_get_the_avatars_web_url()
    {
        $this->assertStringContainsString('http', $this->child->avatarUrl());
        $this->assertStringContainsString($this->child->avatar, $this->child->avatarUrl());
    }

    /** @test */
    public function a_child_can_have_many_activities()
    {
        $this->child->activities()->saveMany([
            new Activity([
                'time' => now()->subMinutes(30),
                'type' => config('enums.activities.sleep')
            ]),
            new Activity([
                'time' => now()->subMinutes(5),
                'type' => config('enums.activities.wake')
            ]),
        ]);

        $this->assertCount(2, $this->child->activities);
        $this->assertInstanceOf(Activity::class, $this->child->activities->first());
    }

    /** @test */
    public function a_childs_age_function_shows_how_old_the_child_is()
    {
        $child1 = Child::factory()->create(['born' => now()->subYear(2)->subMonth(1)]);
        $child2 = Child::factory()->create(['born' => now()->subMonth(2)->subWeek(2)]);

        $this->assertEqualsIgnoringCase('2 years 1 month', $child1->age());
        $this->assertEqualsIgnoringCase('2 months 2 weeks', $child2->age());
    }
}
