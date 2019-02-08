<?php
/**
 * Created by PhpStorm.
 * User: jotoh
 * Date: 2019-02-08
 * Time: 09:17
 */

abstract class TM_Data {

    abstract protected function fetch( $id );

    abstract protected function raw_filter( &$result );

    public $id = -1;

    public $data;

    public function __construct( $mixed ) {

        if ( !isset( $mixed ) )
            return false;

        if( $mixed instanceof TM_Data )
            $this->init( $mixed );

        else
            $this->fetch( $mixed );

    }

    public function init( $data ) {

        $this->data = $data;

        $this->id = (int) $data->id;

        unset( $this->data->id );

    }

}