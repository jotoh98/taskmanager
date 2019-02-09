<?php
/**
 * Created by PhpStorm.
 * User: jotoh
 * Date: 2019-02-08
 * Time: 02:07
 */

/**
 * Class TM_Role
 */
class TM_Role extends TM_Data {

    /**
     * Unique fetch to create TM_Role object from sql data.
     * @param $id
     * @return mixed|void
     */
    protected function fetch($id ) {

        global $tm_db_prefix;

        /**
         * Check if given ident is id or name.
         * And fetch a single object.
         */
        $ask_row = ( is_numeric( $id ) ? 'id' : 'name');
        $data = tmdb_fetch_obj ( "SELECT * FROM {$tm_db_prefix}role WHERE {$ask_row}={$id}", true );

        if ( !$data ) return;

        $this->raw_filter ( $data );

        $this->init ( $data );


    }

    protected function raw_filter( &$result ) {

        global $tm_db_prefix;

        $caps = tmdb_fetch_obj ( "SELECT ranking, capability FROM {$tm_db_prefix}role_capabilities WHERE role_id={$result->id}" );


        foreach ( $caps as $cap )
            unset ( $cap->role_id );

        $result->capabilities = $caps;

    }

    public function is_able ( $capability ) {

        if ( empty( $capability ) ) return true;

        $rank = $this->get_ranking ( $capability );

        foreach ($this->data->capabilities as $mycaps) {

            if ( $this->get_ranking( $mycaps ) >= $rank )
                return true;

        }

        return false;

    }

    public function get_ranking ( $capability ) {

        global $tm_db_prefix;

        if ( !empty( $capability ) ) {

            $rank = tmdb_fetch_num ( "SELECT ranking FROM {$tm_db_prefix}role_capabilities WHERE capability='{$capability}'" );

            return $rank[0];

        }

        return -1;

    }

}