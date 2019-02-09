<?php
/**
 * Created by PhpStorm.
 * User: jotoh
 * Date: 2019-02-07
 * Time: 22:42
 */

/**
 * Class TM_User
 */
class TM_User extends TM_Data {

    /**
     * Unique fetch to create TM_Data object from sql data.
     * @param $mixed
     * @return mixed|void
     */
    protected function fetch ( $mixed ) {

        global $tm_db_prefix;

        /**
         * Check if given ident is id or username.
         * And fetch a single object.
         */
        $ask_row = ( is_int( $mixed ) ? "id" : "username");
        $data = tmdb_fetch_obj("SELECT * FROM {$tm_db_prefix}user WHERE {$ask_row}='{$mixed}'", true );

        if ( !$data ) return;

        $this->raw_filter( $data );

        if ( $data )
            $this->init( $data );

    }

    /**
     * Unique filter to prepare sql data for TM_User constructor.
     * @param $result
     * @return mixed|bool|void
     */
    protected function raw_filter ( &$result ) {

        global $tm_db_prefix;

        /**
         * If id of result object is not numeric, then end
         */
        if ( !is_numeric ( $result->id ) )
            return false;

        /**
         * Get emails associated with users id.
         */
        $mails = tmdb_fetch_obj ( "SELECT id, address,created,main FROM {$tm_db_prefix}email WHERE user_id={$result->id}" );


        if ( $mails instanceof stdClass ) {

            /**
             * If $mails is one mail (stdClass object):
             * Work with object and wrap it for email list
             */
            $result->email_address = $mails->address;
            $result->other_emails = [$mails];

        }  else {

            /**
             * If $mails is array of mails:
             * Find first main email for adress key,
             * the other emails wander into other_emails
             */
            foreach ( $mails as $i => $mail )
                if ( $mail->main == '1' ) {
                    $result->email_address = $mail->address;
                    break;
                }

            $result->other_emails = $mails;

        }

        $result->birthday = date( "c", strtotime( $result->birthday ) );

        $result->role = new TM_Role( $result->role_id );

        unset( $result->role_id );

    }

    public function is_able ( $capability ) {

        if ( $this->get( 'role' ) instanceof TM_Role )

            return $this->get( 'role' )->is_able ( $capability );

        else

            return false;

    }

}