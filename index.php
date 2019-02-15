<?php
error_reporting(E_ALL);
require_once( __DIR__.'/tm-load.php' );

if ( is_logged_in () ):
    tm_template_file ( 'index.php' );
else:
    switch ( $_URL[ 0 ] ):
        case 'login':
            tm_template_file ( 'login.php' );
            break;
        default:
            tm_template_file ( 'homepage.php' );
    endswitch;
endif;
echo password_hash ("12345678", PASSWORD_BCRYPT);
tmdb_close ();

tm_console_print ();

