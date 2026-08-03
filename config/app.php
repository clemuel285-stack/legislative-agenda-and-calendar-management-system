<?php
declare(strict_types=1);

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Legislative Agenda and Calendar Management System');
}
if (!defined('APP_SHORT_NAME')) {
    define('APP_SHORT_NAME', 'LACMS');
}
if (!defined('APP_URL')) {
    define('APP_URL', 'http://localhost/lacms');
}
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}
if (!defined('SESSION_NAME')) {
    define('SESSION_NAME', 'lph_session');
}
if (!defined('SESSION_IDLE_TIMEOUT')) {
    define('SESSION_IDLE_TIMEOUT', 8 * 60 * 60);
}
if (!defined('ROLE_ADMIN')) {
    define('ROLE_ADMIN', 'ADMIN');
}
if (!defined('ROLE_STAFF')) {
    define('ROLE_STAFF', 'STAFF');
}
if (!defined('ROLE_COMMITTEE')) {
    define('ROLE_COMMITTEE', 'COMMITTEE');
}

date_default_timezone_set('Asia/Manila');
