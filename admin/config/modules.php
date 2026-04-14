<?php

// Prevent direct access
if (!defined(constant_name: 'ADMIN_INIT')) {
  die('Direct access not permitted');
}

return [
  'blog' => true,
  'gallery' => true,
  'hero' => false,
  'tours' => false,
  'hero_slider' => false,
  'contact' => true,
  'events' => true,
  'our_work' => true,
  'media' => true,
  'forms' => false,
  'pages' => false,
  'testimonials' => false,

  // this is used to show or hide superadmin adn subamdin files or modules
  'roles' => true,

  // it is used to prevent access to teachers,editor,subadmins onlu superadmin can access or login 
  'users' => true,
];
