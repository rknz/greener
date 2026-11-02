<?php
/**
 * A Greener Tomorrow - Namecheap cPanel Hosting Database Configuration
 *
 * HOW TO USE ON NAMECHEAP HOSTING:
 * Option A (Zero-Configuration / Instant Live):
 *   Leave 'enable_mysql' => false. The website will automatically run using
 *   the pre-populated SQLite database in /data/greener.sqlite with zero setup required!
 *
 * Option B (Namecheap cPanel MySQL Database):
 *   1. In Namecheap cPanel -> MySQL Databases, create a database and user.
 *   2. In phpMyAdmin, import /database/greener_namecheap_mysql.sql.
 *   3. Set 'enable_mysql' => true below and fill in your cPanel dbname, username, and password.
 */

return [
    'enable_mysql' => false,
    'host'         => 'localhost',
    'port'         => 3306,
    'dbname'       => 'cpaneluser_greener',
    'username'     => 'cpaneluser_admin',
    'password'     => 'YOUR_CPANEL_DB_PASSWORD',
];

