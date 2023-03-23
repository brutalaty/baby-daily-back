<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Family;
use App\Models\Child;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;

class CreateFamilyTest extends TestCase
{
    use DatabaseMigrations;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    /** @test */
    public function a_guest_cannot_create_a_family()
    {
        $response = $this->postJson(route('families.store'), ['name' => 'test']);

        $response->assertUnauthorized();
    }

    /** @test */
    public function a_user_can_create_a_family()
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('families.store'), [
            'name' => 'test',
            'relation' => 'Father'
        ]);

        $response->assertSuccessful();

        $response->assertJsonPath('data.name', 'test');
    }
}
