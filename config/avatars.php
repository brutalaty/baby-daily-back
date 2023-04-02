<?php

return [
  'path' => [
    'users' => [
      'db' => 'images/avatars/users/',  //when adding to a users avatar column
      'base' => storage_path('app/public/'),  //saving the file to disc
      'testprefix' => 'storage/app/public/'   //running exists tests on the file
    ],
    'children' => [
      'db' => 'images/avatars/children/',
      'base' => storage_path('app/public/'),
      'testprefix' => 'storage/app/public/'
    ]
  ],
  'file' => [
    'type' => '.png'
  ],
];
