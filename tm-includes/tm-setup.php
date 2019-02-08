<?php
require_once ('tm-conn.php');

define('DB_PREFIX', $tm_db_prefix);

require_once ('tm-errors.php');
require_once (__DIR__.'/class/TM_Data.php');
require_once (__DIR__.'/class/TM_User.php');
require_once (__DIR__.'/class/TM_Role.php');
require_once (__DIR__.'/class/TM_Group.php');

$f = explode( "/", $_GET['f'] );

print_b($f);