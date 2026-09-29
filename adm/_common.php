<?php
define('G5_IS_ADMIN', true);
require_once __DIR__ . '/../common.php';
require_once G5_ADMIN_PATH . '/admin.lib.php';

if (function_exists('ra0_ensure_board_extra_fields_text')) {
    ra0_ensure_board_extra_fields_text();
}

if (isset($token)) {
    $token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}

run_event('admin_common');
