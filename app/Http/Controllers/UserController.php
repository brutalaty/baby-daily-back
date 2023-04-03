<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAvatarRequest;

class UserController extends Controller
{

  public function avatar(UpdateAvatarRequest $request)
  {
    $user = auth()->user();
    $file = $request->file('avatar');

    $path = $file->storeAs(
      '/',
      $user->id . '.' . $file->extension(),
      'users'
    );

    $user->updateAvatar($path);
  }
}
