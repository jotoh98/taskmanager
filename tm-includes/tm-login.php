<?php
require_once dirname ( __DIR__ ) . '/tm-load.php';
$id = tm_user_exists ( $_POST[ 'n' ] );
if ( $id && tm_password_correct ( $id, $_POST[ 'p' ] ) ) {
    set_user_cookies ($id);
    print $id;
    header ('Location: /'.$tm_host_path);
}