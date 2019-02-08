<?php
/**
 * Created by PhpStorm.
 * User: jotoh
 * Date: 2019-02-08
 * Time: 02:07
 */

class TM_Role extends TM_Data {

    protected function fetch( $id ) {

        $data = false;

        $pre = tmdb_pre();

        if( is_int( (int) $id ) )
            $data = tmdb_fetch_obj( "SELECT * FROM {$pre}role WHERE id={$id}" );

        if( is_string( $id ) )
            $data = tmdb_fetch_obj( "SELECT * FROM {$pre}role WHERE name={$id}" );

        if( !$data ) return;

        $this->raw_filter( $data );

        $this->init( $data );


    }

    protected function raw_filter( &$result ) {

        $pre = tmdb_pre();

        $caps = tmdb_fetch_obj( "SELECT ranking, capability FROM {$pre}role_capabilities WHERE role_id={$result->id}" );

        foreach ( $caps as $cap )
            unset( $cap->role_id );

        $result->capabilities = $caps;

    }

    public function is_able( $capability ) {

        if( empty( $capability ) ) return true;

        $rank = $this->get_ranking( $capability );

        foreach ($this->capabilities as $mycaps) {

            if( $this->get_ranking( $mycaps ) >= $rank )
                return true;

        }

        return false;

    }

    public function get_ranking( $capability ) {

        if( !empty( $capability ) ) {

            $pre = tmdb_pre();

            $rank = tmdb_fetch_num( "SELECT ranking FROM {$pre}role_capabilities WHERE capability='{$capability}'" );

            return $rank[0];

        }

        return -1;

    }

}