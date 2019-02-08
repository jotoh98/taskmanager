<?php
/**
 * Created by PhpStorm.
 * User: jotoh
 * Date: 2019-02-07
 * Time: 22:42
 */

class TM_User extends TM_Data {

    protected function fetch( $mixed ) {

        $data = false;

        $pre = tmdb_pre();

        if( is_int( $mixed ) )
            $data = tmdb_fetch_obj("SELECT * FROM {$pre}user WHERE id={$mixed}" );
        else
            $data = tmdb_fetch_obj("SELECT * FROM {$pre}user WHERE username='{$mixed}'" );

        if( !$data ) return;

        $this->raw_filter( $data );

        $this->init( $data );

    }

    protected function raw_filter( &$result ) {

        if( is_int( (int) $result->id ) ) {

            $pre = tmdb_pre();

            $mails = tmdb_fetch_obj("SELECT id, address,created FROM {$pre}email WHERE user_id={$result->id}");

            if($mails instanceof stdClass) {

                $result->email_address = $mails->address;

                $result->other_emails = [$mails];

            }  else {

                foreach ($mails as $i => $mail)

                    if ($mail->id == $result->email_address) {

                        $result->email_address = $mail->address;

                        unset($mails[$i]);

                    }

                $result->other_emails = $mails;

            }
        }

        $result->birthday = date( "c", strtotime( $result->birthday ) );

        $result->role = new TM_Role( $result->role_id );

        unset( $result->role_id );

    }

    public function get( $key ) {

        if( isset( $key ) && property_exists( $this->data, $key ) )

            return $this->data->{$key};

        return false;

    }

    public function is_able( $capability ) {

        if($this->get( 'role' ) instanceof TM_Role)

            return $this->get( 'role' )->is_able($capability);

        else

            return false;

    }

}