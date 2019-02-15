<?php
/**
 * Created by PhpStorm.
 * User: jotoh
 * Date: 2019-02-07
 * Time: 22:14
 */

/**
 * Print mixed variable beautiful
 * @param $mixed
 */
function print_b ( $mixed, $fd = false ) {
    echo '<pre>';
    if($fd) echo __FILE__.': <br>';
    if ( gettype ( $mixed ) == 'boolean' )
        var_dump ( $mixed );
    else
        print_r ( $mixed );
    echo '</pre>';
}

/**
 * Checks, if strings given in array exist as keys of the second array
 * @param array $keys
 * @param array $arr
 * @return bool
 */
function array_keys_exists ( array $keys, array $arr ) {
    return !array_diff_key ( array_flip ( $keys ), $arr );
}

/**
 * Retrieve the global mysqli connection
 * @return mysqli
 */
function tmdb () {

    global $tmdb;
    return $tmdb;

}

/**
 * Retrieves the global tmdb mysqli connection and returns a query
 * @param string $query
 * @param int $resultmode
 * @return bool|mysqli_result
 */
function tmdb_query ( $query = '', $resultmode = MYSQLI_STORE_RESULT ) {

    $tmdb = tmdb ();

    return $tmdb->query ( $query );

}

/**
 * Fetch data off the tmdb connection
 * @param string $query
 * @param bool $single
 * @param int $result_type
 * @return array|bool|mixed|object|stdClass
 */
function tmdb_fetch ( $query = '', $single = false, $result_type = MYSQLI_BOTH ) {

    if ( empty( $query ) )
        return false;

    $res = tmdb_query ( $query );

    if ( !$res )
        return false;

    if ( $res->num_rows == 1 || $single )
        if ( $result_type == 'Object' )
            return $res->fetch_object ();
        else
            return $res->fetch_array ( $result_type );


    $ret = [];

    if ( $result_type == 'Object' )
        while ( $row = $res->fetch_object () )
            $ret[] = $row;
    else
        while ( $row = $res->fetch_array ( $result_type ) )
            $ret[] = $row;

    return $ret;
}

/**
 * Wrapper function to retrieve associative mysqli_result.
 * @param string $query
 * @param bool $single
 * @return array|bool|mixed|object|stdClass
 */
function tmdb_fetch_assoc ( $query = '', $single = false ) {

    return tmdb_fetch ( $query, $single, MYSQLI_ASSOC );

}

/**
 * Wrapper function to retrieve numeric mysqli_result.
 * @param string $query
 * @param bool $single
 * @return array|bool|mixed|object|stdClass
 */
function tmdb_fetch_num ( $query = '', $single = false ) {

    return tmdb_fetch ( $query, $single, MYSQLI_NUM );

}

/**
 * Wrapper function to retrieve array of objects as mysqli_result.
 * @param string $query
 * @param bool $single
 * @return array|bool|mixed|object|stdClass
 */
function tmdb_fetch_obj ( $query = '', $single = false ) {

    return tmdb_fetch ( $query, $single, 'Object' );

}

/**
 * Wrapper function to get the number of rows affected by the query
 * @param string $query
 * @return bool|int
 */
function tmdb_num_rows ( $query = '' ) {

    $r = tmdb_query ( $query );

    if ( !$r )
        return false;

    return $r->num_rows;

}

/**
 * Prepares a query on the tmdb connection
 * @param string $query
 * @return mysqli_stmt
 */
function tmdb_prepare ( $query = '' ) {

    global $tmdb;
    return $tmdb->prepare ( $query );

}

function tmdb_escape( $str ) {
    global $tmdb;

    return $tmdb->escape_string ($str);
}

function tmdb_close() {
    global $tmdb;
    $tmdb->close ();
    tm_console (_('Database Connection closed'));
}
/**
 * Creates new user from username and email
 * @param $username
 * @param $email
 * @return int
 */
function tm_create_user ( $username, $email ) {

    global $tm_db_prefix;

    /**
     * if email is invalid, return tm_error code
     */
    if ( !tm_validateEmail ( $email ) ) return 11;


    /**
     * check, if email is already registered
     * if so, return tm_error code
     */
    $mail_exists = tmdb_num_rows ( "SELECT user_id FROM {$tm_db_prefix}email WHERE address='{$email}'" );
    if ( $mail_exists > 0 ) return 12;


    /**
     * check, if username is already registered
     * if so, return tm_error code
     */
    $name_exists = tmdb_num_rows ( "SELECT id FROM {$tm_db_prefix}user WHERE username='{$username}'" );
    if ( $name_exists > 0 ) return 13;

    /**
     * prepare the user and email insertions
     */
    $insert_user = tmdb_prepare ( "INSERT INTO {$tm_db_prefix}user (username) VALUES (?)" );
    $insert_email = tmdb_prepare ( "INSERT INTO {$tm_db_prefix}email (address, user_id) VALUES (?,?)" );
    $insert_user->bind_param ( "s", $username );
    $insert_email->bind_param ( "si", $email, $user_id );

    /**
     * execute the user insertion
     * binding error? return tm_error code
     */
    $r = $insert_user->execute ();
    if ( !$r ) return 14;

    /**
     * get the new user id (through A_I)
     * execute linked email insertion
     * binding error? return tm_error code
     */
    $user_id = $insert_user->insert_id;
    $r = $insert_email->execute ();
    if ( !$r ) return 14;

    /**
     * close both bindings
     */
    $insert_user->close ();
    $insert_email->close ();

    //mail ( $email, _('Welcome to TaskManager'), $message );

}

/**
 * Test if $string is a valid email
 * @param $string
 * @return bool
 */
function tm_validateEmail ( $string ) {
    preg_match (
        '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD',
        $string,
        $out
    );
    return count ( $out ) == 1;
}

function tm_user_exists($string) {
    global $tm_db_prefix;
    $string = tmdb_escape ($string);
    $res = tmdb_query ("SELECT u.id FROM {$tm_db_prefix}user as u LEFT JOIN {$tm_db_prefix}email as e ON e.user_id = u.id WHERE u.username='{$string}' OR e.address='{$string}'");
    if($res->num_rows > 0) {
        $res = $res->fetch_assoc ();
        return $res[ 'id' ];
    }
    return false;
}

function tm_password_correct($id,$password) {
    global $tm_db_prefix;

    $id = intval(tmdb_escape ($id));

    $password = tmdb_escape ($password);

    $res = tmdb_query ("SELECT hash FROM tm_password WHERE user_id='{$id}' AND deleted IS NULL");

    $res = $res->fetch_array ();

    if ($res->num_rows < 0)
        die(_('No password found for this user'));

    return password_verify ($password, $res['hash']);
}

/**
 * Gets first free id used in given table.
 * @param $table
 * @return int
 */
function get_new_ID ( $table ) {
    global $tm_db_prefix;

    $req = tmdb_fetch_num ( "SELECT MAX(id) FROM {$tm_db_prefix}{$table} WHERE 1" );

    return ( $req ? intval ( $req[ 0 ] ) + 1 : -1 );
}

/**
 * Displays the message to a given tm_error code.
 * @param $code
 */
function tm_error ( $code ) {
    global $tm_error;
    if ( $code > 0 )
        echo $tm_error[ $code ][ 'message' ];
}

/**
 * @param string $name
 * @param string $suffix
 */
function tm_template_file ( $name, $suffix = '' ) {
    global $tm_content_url;

    if ( !empty( $suffix ) )
        $suffix = '-' . $suffix;

    include_once ( $tm_content_url . 'template/' . $name . $suffix );
}

function tm_header () {
    tm_template_file ( 'header.php' );
}

function tm_footer () {
    tm_template_file ( 'footer.php' );
}



function tm_load_classes () {
    global $tm_include_url;

    foreach ( [ 'TM_Data', 'TM_User', 'TM_Role', 'TM_Collection' ] as $n )
        require_once ( $tm_include_url . "class/{$n}.php" );

}

//TODO: render interactive text, needs relationship between users
/*function tm_interactive_text ( $search_for = '', $text ) {

    $string_array = array_unique ( str_split ( $search_for, 1 ) );

    foreach ($string_array,$char) {
        switch ($char) {
            case 'h':
                preg_replace('/#([\w.]*)/', '<a href="t/$1">#$1</a>', $text);
                break;
            case 'h':
                preg_replace('/@([\w]*)/', '<a href="t/$1">#$1</a>', $text);
                break;
        }
    }
}*/
/**
 * Tests, if current user is logged in.
 * @return bool
 */
function is_logged_in () {

    global $tmdb;

    /**
     * prevent SQL Injection
     */
    $user_hash = tmdb_escape ( $_COOKIE[ 'user' ] );
    $secret = tmdb_escape ( $_COOKIE[ 'secret' ] );

    /**
     * check, if session is set only once in the database
     */
    $exists = tmdb_query ( "SELECT * FROM tm_session WHERE user_hash='{$user_hash}'" );

    if ( $exists->num_rows != 1 )
        return false;

    /**
     * If there is one session, check if the dates are valid
     * and if the hashes match. #idontwantnohackers
     * We even delete your precious session, beware.
     */
    $data = $exists->fetch_array ();

    if ( strtotime ( $data['first_set'] ) > date ('U') ||
        strtotime ( $data['last_login'] ) > date ('U') ||
        !password_verify ( $data['secret_hash'], $secret ) ) {
        tmdb_query ( "DELETE FROM tm_session WHERE user_hash='{$user_hash}'" );
        return false;
    }

    /**
     * If you are honest, the last_login will be updated
     * and you're free to go on.
     */
    $tmdb->query ( "UPDATE tm_session SET last_login=UTC_TIMESTAMP WHERE user_hash='{$user_hash}'" );

    return true;

}

function set_user_cookies ( $id ) {
    global $tm_host_path;

    if ( !is_numeric ( $id ) )
        return false;

    $public_hash = password_hash ( $id, 1 );
    $secret = random_int ( 1000, 100000000 );

    $secret_hash = password_hash ( $secret, 1 );

    $day = 60 * 60 * 24;
    setcookie ( 'user', $public_hash, time () + $day * 30, '/'.$tm_host_path );
    setcookie ( 'secret', $secret_hash, time () + $day * 30, '/'.$tm_host_path );

    $res = tmdb_query ( "INSERT INTO tm_session VALUES ('{$public_hash}', '{$secret}', UTC_TIMESTAMP, UTC_TIMESTAMP, {$id} )" );

    return $res;
}

function delete_user_cookies() {

    $public_hash = tmdb_escape ($_COOKIE['user']);

    tmdb_query ("DELETE FROM tm_session WHERE user_hash='{$public_hash}'");

    setcookie ('user', null, time()-1);

    setcookie ('secret', null, time()-1);

}

function tm_console($str) {
    global $tm_console_arr;
    if ( constant ( 'TM_DEBUG' ) )
        array_push($tm_console_arr, $str);
}

function tm_console_print() {
    global $tm_console_arr;
    echo '<div id="tm_console">';
    echo implode ("<br>", $tm_console_arr);
    echo '</div>';
}

function tm_login_form($class='tm-login-form') {
    global $tm_includes_path;
    echo "<form class='{$class}' action='{$tm_includes_path}/tm-login.php' method='post'>";
    echo "<input type='text' name='n'>";
    echo "<input type='password' name='p'>";
    echo "<input type='submit'>";
    echo "</form>";
}