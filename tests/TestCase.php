<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

use App\Models\User;
use App\Models\Child;

use Illuminate\Support\Facades\Storage;

use \DateTime;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        //remove avatars
        foreach (User::all() as $user) {
            $user->delete();
        }

        foreach (Child::all() as $child) {
            $child->delete();
        }

        parent::tearDown();
    }

    protected function createUser($attributes = []): User
    {
        return User::factory()->create($attributes);
    }
}
