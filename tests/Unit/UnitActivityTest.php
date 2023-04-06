<?php

namespace Tests\Unit;

use App\Models\Child;
use App\Models\Activity;
use App\Models\Consumption;

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

    /** @test */
    public function an_activity_can_have_consumptions()
    {
        $activity = Activity::factory()->create(['type' => config('enums.activities.medicine')]);
        $activity->consumptions()->saveMany([
            new Consumption(['volume' => 50, 'name' => 'Spaghetti']),
            new Consumption(['volume' => 25, 'name' => 'Garlic Bread']),
        ]);

        $activity->refresh();

        $this->assertInstanceOf(Consumption::class, $activity->consumptions->first());
        $this->assertCount(2, $activity->consumptions);
    }
}
