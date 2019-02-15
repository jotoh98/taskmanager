<?php
/**
 * TM_Collection Class File
 * User: jotoh
 * Date: 2019-02-08
 * Time: 02:34
 */

/**
 * Class TM_Collection
 */
class TM_Collection extends TM_Data {

    /**
     * Unique fetch to create TM_Collection object from sql data.
     * @param $id
     * @return mixed|void
     */
    protected function fetch( $id ) {

        global $tm_db_prefix;

        if( !is_int( $id ) ) return;
        
        $data = tmdb_fetch_obj( "SELECT * FROM {$tm_db_prefix}group WHERE id={$id}" );

        if( !is_int( (int) $data->id ) ) return;

        $this->raw_filter( $data );

        $this->init( $data );

    }

    /**
     * Unique filter to prepare sql data for TM_Collection constructor.
     * @param $result
     * @return mixed|void
     */
    protected function raw_filter( &$result ) {

        global $tm_db_prefix;
        
        $members = tmdb_fetch_num(
            "SELECT {$tm_db_prefix}user.id FROM {$tm_db_prefix}user
                    LEFT JOIN {$tm_db_prefix}user_in_group ON {$tm_db_prefix}user.id = {$tm_db_prefix}user_in_group.user_id
                    WHERE {$tm_db_prefix}user_in_group.group_id={$result->id}"
        );

        $result->members = array_map( function($e) {
            return $e[0];
        }, $members );

        $result->children = $this->get_children();

        $result->edit_privilege = new TM_Role($result->edit_privilege);

        $result->enter_privilege = new TM_Role($result->enter_privilege);

    }

    /**
     * Get numeric Array of TM_Collection ids.
     * @return array|bool|mixed|object|stdClass
     */
    public function get_children() {
        
        global $tm_db_prefix;
        
        return tmdb_fetch_num( "SELECT child FROM {$tm_db_prefix}group_composite WHERE parent={$this->ID}" );
        
    }

    /**
     * Add children to TM_Collection in database.
     * @param int $id
     * @return bool
     */
    public function add_children( $id = -1 ) {

        global $tm_db_prefix;

        $parentID = -1;

        if( $this instanceof TM_Collection)
            $parentID = $this->ID;

        if( $id < 0 || $parentID < 0 )
            return false;
        
        $test = tmdb_prepare( "SELECT COUNT(parent) FROM {$tm_db_prefix}group_composite WHERE parent={$parentID} AND child=?" );
        $test->bind_param("i", $child);

        $res = tmdb_prepare( "INSERT INTO {$tm_db_prefix}group_composite (parent, child) VALUES ({$parentID},?)");
        $res->bind_param("i", $child);


        foreach (func_get_args() as $child) {
            if( $child instanceof TM_Collection )
                $child = $child->ID;


            if( is_numeric($child) ) {
                $test->execute();
                if( $test->get_result()->num_rows < 1 )
                    $res->execute();
            }

        }

        $test->close();
        $res->close();

        return true;
    }

}