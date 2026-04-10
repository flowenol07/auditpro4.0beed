<?php

// namespace Model;

use Core\Model;

class PasswordPolicyModel extends Model {

    protected static $table = 'password_policy';

    public function emptyInstance() {
        return $this -> _db::getTableColumns( self::$table );
    }
    
    public function getTableName() {
        return self::$table;
    }

    public function getSinglePasswordPolicy($filters = [], $query_type = null, $sql = '')
    {
        return $this -> _db::selectSingle(self::$table, $filters);
    }
}

?>