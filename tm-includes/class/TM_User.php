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

    /**
     * Unique filter to prepare sql data for TM_User constructor.
     * @param $result
     * @return mixed|void
     */
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

    public function is_able( $capability ) {

        if($this->get( 'role' ) instanceof TM_Role)

            return $this->get( 'role' )->is_able($capability);

        else

            return false;

    }

}