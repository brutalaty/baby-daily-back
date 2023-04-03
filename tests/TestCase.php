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

    /**
     * @return String DateTime::ATOM formatted date string
     */
    protected function date_atom_string_from_today_subtracting(String $dateIntervalString)
    {
        return date_sub(new DateTime(), date_interval_create_from_date_string($dateIntervalString))->format(DateTime::ATOM);
    }

    /**
     * @return String Y-m-d formatted date String
     */
    protected function date_string_from_today_subtracting(String $dateIntervalString)
    {
        return date_sub(new DateTime(), date_interval_create_from_date_string($dateIntervalString))->format('Y-m-d');
    }
}
