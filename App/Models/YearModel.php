<?php

// namespace Model;

use Core\Model;

class YearModel extends Model {

    protected static $table = 'year_master';

    public function emptyInstance() {
        return $this -> _db::getTableColumns( self::$table );
    }

    public function getAllYears($filters = [], $query_type = null, $sql = '')
    {
        //database helper function call
        return get_all_data_query_builder(2, $this -> _db, self::$table, $filters, $query_type, $sql);
    }

    public function getSingleYear($filters = [], $query_type = null, $sql = '')
    {
        //database helper function call
        return get_all_data_query_builder(1, $this -> _db, self::$table, $filters, $query_type, $sql);
    }

    public function getTableName() {
        return self::$table;
    }
}

?>