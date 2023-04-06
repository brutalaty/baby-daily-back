<?php

namespace Tests\Unit;

use App\Models\Child;
use App\Models\Activity;
use App\Models\Consumption;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UnitConsumptionTest extends TestCase
{
    use DatabaseMigrations;

    private Child $child;
    private Activity $activity;
    private Consumption $consumption;

    /** @test */
    public function a_consumption_belongs_to_an_activity()
    {

        $consumption = Consumption::factory()->create();
        $this->assertInstanceOf(Activity::class, $consumption->activity);
    }
}
