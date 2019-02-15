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
     * Database identifier (primary key).
     * @var int
     */
    public $ID = -1;


    /**
     * Universal data object
     * @var stdClass
     */
    private $data;

    /**
     * Abstract fetch enforces unique raw data fetch from database and creates TM_Data object.
     * @param $ID
     * @return mixed
     */
    abstract protected function fetch ( $ID );

    /**
     * Abstract raw_filter enforces unique filter for fetched sql data.
     * @param $result
     * @return mixed
     */
    abstract protected function raw_filter ( &$result );

    /**
     * Abstract sql_update, enforces unique update of sql data.
     * If $key is null: Update sql based on TM_Data calling the method.
     * If $key is string or array: Update just this/these key/s.
     * @param null|string|array $key
     * @return mixed
     */
    // abstract protected function sql_update( $key = null );
    // TODO: add sql_update function to relevant classes

    /**
     * TM_Data constructor.
     * Expects no specific type, copies given TM_Data object.
     * @param $mixed
     */
    public function __construct ( $mixed ) {

        if ( !isset( $mixed ) )
            return false;

        if ( $mixed instanceof TM_Data && isset( $mixed->data->ID ) )
            $this->init ( $mixed->data );

        else if ( is_object ( $mixed ) )
            $this->init ( $mixed );

        else
            $this->fetch ( $mixed );

    }

    /**
     * Initialize TM_Data object by setting data and id.
     * Expects well-formed data object with ID as parameter.
     * @param stdClass $data
     */
    public function init ( $data ) {

        $this->data = $data;

        $this->ID = (int)$data->ID;

    }

    /**
     * Magical isset method.
     * Checks if data exists in TM_Data.
     * @param $key
     * @return bool
     */
    public function __isset ( $key ) {

        if ( 'id' == $key )
            $key = 'ID';

        return isset( $this->data->$key );

    }

    /**
     * Magical get method.
     * Get any value stored in TM_Data.
     * @param $key
     * @return mixed|bool
     */
    public function __get ( $key ) {

        if ( 'id' == $key )
            return $this->ID;

        elseif ( isset( $this->data->$key ) )
            return $this->data->$key;

        return null;

    }

    /**
     * Magical set method.
     * Set any value stored in TM_Data.
     * @param $key
     * @param $value
     * @return void
     */
    public function __set ( $key, $value ) {

        if ( 'id' == $key )
            $this->ID = $value;

        else
            $this->data->$key = $value;

    }

    /**
     * Magical unset method.
     * Unset any variable in TM_Data.
     * @param $key
     */
    public function __unset ( $key ) {

        if ( 'id' == $key )
            unset( $this->ID );

        elseif ( isset( $this->data->$key ) )
            unset( $this->data->$key );

    }

    /**
     * Wrapper function for __isset.
     * @param $key
     * @return mixed
     */
    public function has_prop( $key) {
        return $this->isset($key);
    }

    /**
     * Wrapper function for __get.
     * @param $key
     * @return bool|mixed
     */
    public function get( $key ) {
        return $this->__get($key);
    }

    /**
     * Wrapper function for __set.
     * @param $key
     * @param $value
     */
    public function set( $key, $value) {
        $this->__set ($key,$value);
    }

    /**
     * Wrapper function for __unset.
     * @param $key
     */
    public function unset_prop( $key) {
        $this->__unset ($key);
    }
    /**
     * Wrapper function ID Getter.
     * @return int
     */
    public function getID () {
        return $this->ID;
    }

    /**
     * Wrapper function data Getter.
     * @return stdClass
     */
    public function getData () {
        return $this->data;
    }

    /**
     * Returns array representation.
     * @return array
     */
    public function to_array() {
        return get_object_vars ($this->data);
    }
}