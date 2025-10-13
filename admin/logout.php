<?php
/**
 * Admin Logout
 */

require_once __DIR__ . '/../config/config.php';

unset($_SESSION['is_admin']);
unset($_SESSION['admin_username']);

redirect(APP_URL . '/admin/login.php');

