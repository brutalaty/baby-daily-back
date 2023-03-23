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
}
