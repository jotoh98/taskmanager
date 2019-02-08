<?php
/**
 * TM_Data Class File
 * User: jotoh
 * Date: 2019-02-08
 * Time: 09:17
 */

/**
 * Class TM_Data
 * Abstract class for data connection between Class and SQL structure.
 */
abstract class TM_Data {

    /**
     * Abstract fetch, enforces unique raw data fetch from database and creates TM_Data object.
     * @param $id
     * @return mixed
     */
    abstract protected function fetch( $id );

    /**
     * Abstract raw_filter, enforces unique filter for fetched sql data.
     * @param $result
     * @return mixed
     */
    abstract protected function raw_filter( &$result );

    /**
     * Database identifier (primary key).
     * @var int
     */
    public $id = -1;

    /**
     * Universal data object
     * @var stdClass
     */
    public $data;

    /**
     * TM_Data constructor.
     * Expects no specific type, copies given TM_Data object.
     * @param $mixed
     */
    public function __construct( $mixed ) {

        if ( !isset( $mixed ) )
            return false;

        if( $mixed instanceof TM_Data )
            $this->init( $mixed );

        else
            $this->fetch( $mixed );

    }

    /**
     * Initialize TM_Data object by setting data and id.
     * Expects well-formed data object with id as parameter.
     * @param stdClass $data
     */
    public function init( $data ) {

        $this->data = $data;

        $this->id = (int) $data->id;

        unset( $this->data->id );

    }

    /**
     * Get any value stored in data object.
     * @param $key
     * @return bool
     */
    public function get( $key ) {

        if( isset( $key ) && property_exists( $this->data, $key ) )

            return $this->data->{$key};

        return false;

    }

}