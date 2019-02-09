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
function print_b ( $mixed ) {
    echo '<pre>';
    if(gettype($mixed) == 'boolean')
        var_dump($mixed);
    else
        print_r( $mixed );
    echo '</pre>';
}

/**
 * Checks, if strings given in array exist as keys of the second array
 * @param array $keys
 * @param array $arr
 * @return bool
 */
function array_keys_exists ( array $keys, array $arr ) {
    return !array_diff_key( array_flip($keys), $arr );
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
function tmdb_query ( $query = '', $resultmode = MYSQLI_STORE_RESULT  ) {

    $tmdb = tmdb();

    return $tmdb->query( $query );

}

/**
 * Fetch data off the tmdb connection
 * @param string $query
 * @param bool $single
 * @param int $result_type
 * @return array|bool|mixed|object|stdClass
 */
function tmdb_fetch ( $query = '', $single = false, $result_type = MYSQLI_BOTH) {

    if ( empty( $query ) )
        return false;

    $res = tmdb_query( $query );

    if ( !$res )
        return false;

    if ( $res->num_rows == 1 || $single )
        if ( $result_type == 'Object' )
            return $res->fetch_object();
        else
            return $res->fetch_array( $result_type );


    $ret = [];

    if($result_type == 'Object')
        while( $row = $res->fetch_object() )
            $ret[] = $row;
    else
        while( $row = $res->fetch_array($result_type) )
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

    return tmdb_fetch( $query, $single, MYSQLI_ASSOC );

}

/**
 * Wrapper function to retrieve numeric mysqli_result.
 * @param string $query
 * @param bool $single
 * @return array|bool|mixed|object|stdClass
 */
function tmdb_fetch_num ( $query = '', $single = false ) {

    return tmdb_fetch( $query, $single, MYSQLI_NUM );

}

/**
 * Wrapper function to retrieve array of objects as mysqli_result.
 * @param string $query
 * @param bool $single
 * @return array|bool|mixed|object|stdClass
 */
function tmdb_fetch_obj ( $query = '', $single = false ) {

    return tmdb_fetch( $query, $single, 'Object' );

}

/**
 * Wrapper function to get the number of rows affected by the query
 * @param string $query
 * @return bool|int
 */
function tmdb_num_rows ($query = '' ) {

    $r = tmdb_query( $query );

    if(!$r)
        return false;

    return $r->num_rows;

}

/**
 * Prepares a query on the tmdb connection
 * @param string $query
 * @return mysqli_stmt
 */
function tmdb_prepare ($query = '' ) {

    global $tmdb;
    return $tmdb->prepare( $query );

}

/**
 * @return string
 */
function tmdb_pre () {
    global $tm_db_prefix;
    return $tm_db_prefix;
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
    if( !tm_validateEmail( $email ) ) return 11;


    /**
     * check, if email is already registered
     * if so, return tm_error code
     */
    $mail_exists = tmdb_num_rows("SELECT user_id FROM {$tm_db_prefix}email WHERE address='{$email}'");
    if( $mail_exists > 0 ) return 12;


    /**
     * check, if username is already registered
     * if so, return tm_error code
     */
    $name_exists = tmdb_num_rows("SELECT id FROM {$tm_db_prefix}user WHERE username='{$username}'");
    if ( $name_exists > 0 ) return 13;

    /**
     * prepare the user and email insertions
     */
    $insert_user = tmdb_prepare("INSERT INTO {$tm_db_prefix}user (username) VALUES (?)");
    $insert_email = tmdb_prepare("INSERT INTO {$tm_db_prefix}email (address, user_id) VALUES (?,?)");
    $insert_user->bind_param( "s", $username );
    $insert_email->bind_param( "si", $email,  $user_id);

    /**
     * execute the user insertion
     * binding error? return tm_error code
     */
    $r = $insert_user->execute();
    if( !$r ) return 14;

    /**
     * get the new user id (through A_I)
     * execute linked email insertion
     * binding error? return tm_error code
     */
    $user_id = $insert_user->insert_id;
    $r = $insert_email->execute();
    if( !$r ) return 14;

    /**
     * close both bindings
     */
    $insert_user->close();
    $insert_email->close();

    //mail ( $email, _('Welcome to TaskManager'), $message );

}

/**
 * Test if $string is a valid email
 * @param $string
 * @return bool
 */
function tm_validateEmail ( $string ) {
    preg_match(
        '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD',
        $string,
        $out
    );
    return count($out) == 1;
}

/**
 * Gets first free id used in given table.
 * @param $table
 * @return int
 */
function get_new_ID ($table ) {
    global $tm_db_prefix;

    $req = tmdb_fetch_num("SELECT MAX(id) FROM {$tm_db_prefix}{$table} WHERE 1");

    return ( $req ? intval( $req[0] ) + 1 : -1 );
}

/**
 * Displays the message to a given tm_error code.
 * @param $code
 */
function tm_error ($code ) {
    global $tm_error;
    if( $code > 0 )
        echo $tm_error[$code]['message'];
}

/**
 * @param string $name
 * @param string $suffix
 */
function tm_template_file ($name, $suffix = '' ) {
    global $tm_content_url;

    if ( !empty( $suffix ) )
        $suffix = '-' . $suffix;

    include_once ( $tm_content_url . 'template/' . $name . $suffix );
}

function tm_header () {
    tm_template_file ('header.php' );
}

function tm_footer () {
    global $tmdb;

    tm_template_file ('footer.php' );
    $tmdb->close();
    if ( constant ('TM_DEBUG' ) )
        echo 'Database connection closed';
}

function tm_load_classes () {
    global $tm_include_url;

    foreach ( ['TM_Data', 'TM_User', 'TM_Role', 'TM_Group'] as $n )
        require_once ( $tm_include_url."class/{$n}.php");

}