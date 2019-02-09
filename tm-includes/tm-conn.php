<?php
/**
 * set the locale
 */
putenv('LC_ALL='.$tm_lang);
setlocale(LC_ALL, $tm_lang);

bindtextdomain("lang", "tm-includes/lang");

/**
 * .mo filename
 */
textdomain("lang");


/**
 * check database credentials
 */
if(empty($tm_db_user))
    die(_('No username for database connection given'));

if(empty($tm_db_name))
    die(_('No database name given'));

if(empty($tm_db_host))
    $tm_db_host = 'localhost';

/**
 * establish central database connection
 */
$tmdb = new mysqli($tm_db_host, $tm_db_pass, $tm_db_user, $tm_db_name);

if($tmdb->connect_error)
    die(_('Database connection error').'('.$tmdb->connect_errno.')');
elseif (TM_DEBUG)
    echo _('Database connection established').'<br>';