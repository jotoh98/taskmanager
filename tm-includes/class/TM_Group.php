<?php
/**
 * Created by PhpStorm.
 * User: jotoh
 * Date: 2019-02-08
 * Time: 02:34
 */

class TM_Group extends TM_Data {

    protected function fetch( $id ) {

        if( !is_int( $id ) ) return;
        
        $pre = tmdb_pre();
        
        $data = tmdb_fetch_obj( "SELECT * FROM {$pre}group WHERE id={$id}" );

        if( !is_int( (int) $data->id ) ) return;

        $this->raw_filter( $data );

        $this->init( $data );
    }

    protected function raw_filter( &$result ) {

        $pre = tmdb_pre();
        
        $members = tmdb_fetch_num(
            "SELECT {$pre}user.id FROM {$pre}user
                    LEFT JOIN {$pre}user_in_group ON {$pre}user.id = {$pre}user_in_group.user_id
                    WHERE {$pre}user_in_group.group_id={$result->id}"
        );

        $result->members = array_map( function($e) {
            return $e[0];
        }, $members );

        $result->children = $this->get_children();

        $result->edit_privilege = new TM_Role($result->edit_privilege);

        $result->enter_privilege = new TM_Role($result->enter_privilege);

    }


    public function get_children() {
        
        $pre = tmdb_pre();
        
        return tmdb_fetch_num( "SELECT child FROM {$pre}group_composite WHERE parent={$this->id}" );
        
    }

    public function add_children( $id = -1 ) {
        $parentID = -1;
        if( $this instanceof TM_Group)
            $parentID = $this->id;

        if( $id < 0 || $parentID < 0 )
            return false;

        $pre = tmdb_pre();
        
        $test = tmdb_prepare( "SELECT COUNT(parent) FROM {$pre}group_composite WHERE parent={$parentID} AND child=?" );
        $test->bind_param("i", $child);

        $res = tmdb_prepare( "INSERT INTO {$pre}group_composite (parent, child) VALUES ({$parentID},?)");
        $res->bind_param("i", $child);


        foreach (func_get_args() as $child) {
            if( $child instanceof TM_Group )
                $child = $child->ID;


            if( is_numeric($child) ) {
                $test->execute();
                if( $test->get_result()->num_rows < 1 )
                    $res->execute();
            }

        }

        $test->close();
        $res->close();


    }

}