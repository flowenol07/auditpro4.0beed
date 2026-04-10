<?php

namespace Core;

use PDO;
use PDOException;

trait Database
{
    protected static $DBH;
    private static $transactionStarted = false;

    protected static function openConnectionHandler()
    {
        try {

            if (!isset(self::$DBH) || !(self::$DBH instanceof PDO)) {
                self::$DBH = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
                    DB_USER,
                    DB_PASS
                );
            }

            return self::$DBH;

        } catch (PDOException $e) {

            die('Connection failed: ' . $e->getMessage());
        }
    }

    protected static function closeConnectionHandler() {

        if (isset(self::$DBH))
            self::$DBH = null;
    }

    protected static function instanceHandler() {

        if (!isset(self::$DBH))
            self::$DBH = self::openConnectionHandler();

        return static::class;
    }

    protected static function lastInsertIdHandler()
    {
        try {
            return self::$DBH->lastInsertId();
        } catch (PDOException $e) {
            return null;
        }
    }

    protected static function getTableColumnsHandler($tableName, $default = 'obj') {

        $columns = [];

        try {
            
            $DBH = self::instanceHandler();

            $sql = "SHOW COLUMNS FROM " . $tableName;
            $stmt = self::$DBH -> prepare($sql);
            $stmt -> execute();
            $result = $stmt -> fetchAll(PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $columns[ $row['Field'] ] = null;
            }

        } catch (PDOException $e) {
            // Handle the exception (log, display error, etc.)
            die('Query failed: ' . $e -> getMessage());
        }

        $columns = ($default == 'obj') ? json_decode( json_encode($columns) ) : $columns;
        return $columns;
    }

	protected static function beginTransactionHandler() {
        self::$DBH -> beginTransaction();
        self::$transactionStarted = true;
    }

	protected static function commitHandler() {
        self::$DBH -> commit();
        self::$transactionStarted = false;
    }

	protected static function rollbackHandler() {

        if (self::$transactionStarted) {
            self::$DBH -> rollBack();
            self::$transactionStarted = false;
        }
    }

	protected static function executeQueryHandler($sql, $where = []) {
        
		try {

            // print_r(self::$DBH);
            // print_r($where);
			// echo $sql;            
            // echo '<hr />';

            $DBH = self::instanceHandler();

            $stmt = self::$DBH -> prepare($sql);

            if( is_array($where) && array_key_exists('params', $where) && sizeof($where['params']) > 0 )
            {
                foreach ($where['params'] as $key => $value) {
                    $stmt -> bindValue(":$key", $value);
                }
            }

            $stmt -> execute();
            return $stmt;

        } catch (PDOException $e) {

            self::rollbackHandler();
            die('Query failed: ' . $e->getMessage());
        }
    }

    protected static function buildSetClauseHandler(array $data): string {

        $setClause = '';

        foreach ($data as $key => $value) {
            $setClause .= "$key = :$key, ";
        }

        // Remove the trailing comma and space
        return rtrim($setClause, ', ');
    }

	protected static function buildWhereClauseHandler($where) {

        if (empty($where)) {
            return '';
        }

        $returnData = ['where' => '', 'params' => [] ];

        //trim data
        if(is_array($where) && array_key_exists('where', $where) && !empty( $where['where'] ))
        {
            $returnData['where'] = ' WHERE ' . trim_str($where['where']);

            //assign parameters
            if(array_key_exists('params', $where) && !empty( $where['params'] ))
                $returnData['params'] = $where['params'];
        }           

        return $returnData;
    }

	protected static function jsonEncodeHandler($value) {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected static function countRowsHandler($table, $where = []) {
        $sql = "SELECT COUNT(*) as count FROM $table ";
        $where = self::buildWhereClauseHandler($where);
    
        if (!empty($where) && array_key_exists('where', $where) && !empty($where['where'])) {
            $sql .= $where['where'];
        }
    
        $stmt = self::executeQueryHandler($sql, $where);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

	// ---------- SELECT ----------

	protected static function selectSingleHandler($table, $where = [], $select = '*', $returnObject = true) {

		if($table != 'sql')
            $sql = "SELECT $select FROM $table ";
        else
            $sql = $select;

        //reassign where
        $where = self::buildWhereClauseHandler($where);

        if(!empty($where) && array_key_exists('where', $where) && !empty($where['where']))
            $sql .= $where['where'] ." LIMIT 1";

        $stmt = self::executeQueryHandler($sql, $where);
        $result = $returnObject ? $stmt -> fetchObject() : $stmt -> fetch(PDO::FETCH_ASSOC);
        return $result ? $result : null;
    }

    protected static function selectMultipleHandler($table, $where = [], $select = '*', $returnObject = true) {

        if($table != 'sql')
            $sql = "SELECT $select FROM $table ";
        else
            $sql = $select;

        //reassign where
        $where = self::buildWhereClauseHandler($where);

        if(!empty($where) && array_key_exists('where', $where) && !empty($where['where']))
            $sql .= $where['where'] ."";
        
        $stmt = self::executeQueryHandler($sql, $where);
        return $returnObject ? $stmt->fetchAll(PDO::FETCH_OBJ) : $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

	// ---------- INSERT ----------
	
	protected static function insertHandler($table, $data, $created_at_data = true) {

        if($created_at_data)
        {
            // Auto update timestamps
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        // Convert array to JSON string
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $data[$key] = json_encode($value);
            }
        }

        //reassign where
        $where = self::buildWhereClauseHandler(['params']);

        //merge 
        $where['params'] = array_merge($data, $where['params']);

        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO $table ($columns) VALUES ($values)";
        $stmt = self::executeQueryHandler($sql, $where);
        return $stmt -> rowCount();
    }

	protected static function insertMultipleHandler($table, $dataList, $lastInsert = false) {
        
        self::instanceHandler();
		self::beginTransactionHandler();

        try {

            $lastInsertIds = [];

            foreach ($dataList as $data) {
                self::insertHandler($table, $data);

                if( $lastInsert ) // if true
                    $lastInsertIds[] = self::lastInsertIdHandler();

            }

            self::commitHandler();

            if( $lastInsert ) // if true
                return $lastInsertIds;

            return true;

        } catch (Exception $e) {
            self::rollbackHandler();
            return false;
        }
    }

	// ---------- UPDATE ----------

	protected static function updateHandler($table, $data, $where = []) {

        // Auto update timestamps
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Convert array to JSON string
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $data[$key] = json_encode($value);
            }
        }

        $setClause = self::buildSetClauseHandler($data);
        $whereClause = self::buildWhereClauseHandler($where);

        //reassign where
        $where = $whereClause;

        $sql = "UPDATE $table SET $setClause  ". $whereClause['where'] ."";

        //merge 
        $where['params'] = array_merge($data, $where['params']);

        $stmt = self::executeQueryHandler($sql, $where);
        return $stmt -> rowCount();
    }
	
    protected static function updateMultipleHandler($table, $dataList, $whereList) {

        self::instanceHandler();
        self::beginTransactionHandler();

        try {
            foreach ($dataList as $c_index => $data) {
                // $where = [$whereColumn => $data[$whereColumn]];
                self::updateHandler($table, $data, $whereList[ $c_index ]);
            }

            self::commitHandler();
            return true;

        } catch (Exception $e) {
            self::rollbackHandler();
            return false;
        }
    }

	// ---------- DELETE ----------

	protected static function deleteHandler($table, $where = []) {

        // Auto update timestamps
        $data['deleted_at'] = date('Y-m-d H:i:s');

        //method call
        return self::updateHandler($table, $data, $where);
    }

	protected static function deleteMultipleHandler($table, $dataList, $whereList) {

        self::instanceHandler();
        self::beginTransactionHandler();

        try {
            foreach ($dataList as $c_index => $data) {
                // $where = [$whereColumn => $id];
                self::deleteHandler($table, $whereList[ $c_index ]);
            }

            self::commitHandler();
            return true;

        } catch (Exception $e) {
            self::rollbackHandler();
            return false;
        }
    }

    // function for insert bulk data
    protected static function accInsertBulkDataHandler($table, $dump_data_array, $dumpType) 
    {
        self::instanceHandler();
        self::beginTransactionHandler();

        try {

            // Auto update timestamps
            $created_at = date('Y-m-d H:i:s');
            $updated_at = date('Y-m-d H:i:s');
            
            $total_records = sizeof($dump_data_array);
            $per_records = 25;

            $data = array();
            $first_array = array();
            $insert_record_cnt = 0;
            $placeholder_cnt = 1;

            function create_placeholder($first_array, $cnt) {

                $str = '(';

                foreach(array_keys($first_array) as $table_key)
                    $str .= ':' . $table_key . '_' . $cnt . ',';

                $str = substr($str, 0, -1);

                $str .= '),';

                return $str;
            }

            //generate records as per record limit
            foreach($dump_data_array as $c_dump_data)
            {
                //unset vars
                unset(
                    $c_dump_data['error'], $c_dump_data['branch_code'], 
                    $c_dump_data['branch_name'], $c_dump_data['scheme_code'], 
                    $c_dump_data['scheme_name']
                );

                //insert created and updated at
                $c_dump_data['created_at'] = $created_at;
                $c_dump_data['updated_at'] = $updated_at;

                //first entry
                if(empty($first_array))
                    $first_array = $c_dump_data;

                $columns = implode(', ', array_keys($first_array));
                
                if(!array_key_exists($insert_record_cnt, $data))
                {
                    //first attempt value insert
                    $data[ $insert_record_cnt ] = [
                        'query' => null, 'dump' => []
                    ];

                    if($dumpType == 2)
                        $data[ $insert_record_cnt ]['query'] = "INSERT INTO dump_advances ( ". $columns ." ) VALUES ";
                    else
                        $data[ $insert_record_cnt ]['query'] = "INSERT INTO dump_deposits ( ". $columns ." ) VALUES ";
                }

                //push data
                $data[ $insert_record_cnt ]['dump'][] = $c_dump_data;
                // $data[ $insert_record_cnt ]['query'] .= create_ques_string( count(array_keys($first_array)) ) . ",";

                $data[ $insert_record_cnt ]['query'] .= create_placeholder($first_array, $placeholder_cnt);
                $placeholder_cnt++;

                if(sizeof($data[ $insert_record_cnt ]['dump']) == $per_records)
                {
                    $data[ $insert_record_cnt ]['query'] = substr($data[ $insert_record_cnt ]['query'], 0, -1);
                    $insert_record_cnt++;
                    $placeholder_cnt = 1;
                }
            }

            $insert_row_cnt = 0;

            foreach($data as $c_cnt_key => $c_data)
            {
                // print_r( $c_data['query']);
                // echo '<br /><br />';

                //odd number last comma remove
                $c_data['query'] = (sizeof($c_data['dump']) % $per_records != 0) ? substr( $c_data['query'], 0, -1 ) : $c_data['query'];

                $stmt = self::$DBH -> prepare($c_data['query']);
                
                foreach ($c_data['dump'] as $cIndex => $cAccDetails) {

                    foreach ($cAccDetails as $key => $value)
                    {
                        // echo (":" . $key . "_" . ($cIndex + 1) ) . ' =>' . $value . '<br />';
                        $stmt -> bindValue(( ":" . $key . "_" . ($cIndex + 1) ), $value);
                    }
                }

                $stmt -> execute();
                $insert_row_cnt += $stmt -> rowCount();

                // break;
            }

            if( $insert_row_cnt != $total_records )
            {
                self::rollbackHandler();    
                return false;
            }

            self::commitHandler();
            return true;
            
        } catch (Exception $e) {
            // return 'Error: ' . $e -> getMessage();
            self::rollbackHandler();
            return false;
        }
    }
}

?>