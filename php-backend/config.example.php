<?php
// Copy to config.php and update for your PHP hosting. Never commit config.php.
return [
  'db_host' => 'localhost',
  'db_name' => 'yb_gadgets',
  'db_user' => 'yb_db_user',
  'db_password' => 'YOUR_DATABASE_PASSWORD',
  'admin_username' => 'yb_admin',
  // Generate: php -r "echo password_hash('YOUR_LONG_UNIQUE_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
  'admin_password_hash' => 'PASTE_GENERATED_PASSWORD_HASH_HERE',
];
