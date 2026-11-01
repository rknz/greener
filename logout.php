<?php
/**
 * A Greener Tomorrow - Safe Session Revocation & Logout
 */

require_once __DIR__ . '/config/config.php';
require_once AGY_ROOT . '/includes/functions.php';
require_once AGY_ROOT . '/includes/Auth.php';

Auth::logout();
redirect(BASE_URL . '/login.php?logged_out=1');

