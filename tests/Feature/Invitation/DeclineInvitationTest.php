<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Family;

use App\Mail\FamilyInvitation;

use Illuminate\Support\Facades\Mail;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;

class DeclineInvitationTest extends TestCase
{
  use DatabaseMigrations;

  protected $user;

  public function setUp(): void
  {
    parent::setUp();
    $this->user = $this->createUser();
  }
}
