<?php

namespace Tests\Unit;

use App\Models\Child;
use App\Models\Activity;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UnitActivityTest extends TestCase
{
    use DatabaseMigrations;

    private Child $child;
    private Activity $activity;

    /** @test */
    public function an_activity_belongs_to_a_child()
    {
        $activity = Activity::factory()->create();
        $this->assertInstanceOf(Child::class, $activity->child);
    }
}
