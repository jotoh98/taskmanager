<?php


require_once ( __DIR__ . '/tm-config.php' );

$tm_root_path = __DIR__;

$tm_include_url = $tm_root_path . '/' . $tm_includes_path . '/';
$tm_content_url = $tm_root_path . '/' . $tm_content_path . '/';

$tm_console_arr = array();

require_once ( $tm_include_url . 'tm-conn.php' );
require_once ( $tm_include_url . 'tm-functions.php' );
require_once ( $tm_include_url . 'tm-errors.php' );

tm_load_classes ();

$_URL = explode ( "/", $_GET[ 'f' ] );
