<?php

namespace Core;

use Core\Database;

class DBConnection {
    
    use Database;
    
    public static function openConnection() {
        return self::openConnectionHandler();
    }

    public static function closeConnection() {
        self::closeConnectionHandler();
    }

    public static function getDBH() {
        return self::$DBH;
    }

    public static function instance() {
        return self::instanceHandler();
    }

    public static function lastInsertId() {
        return self::lastInsertIdHandler();
    }

    public static function getTableColumns($tableName, $default = 'obj') {
        return self::getTableColumnsHandler($tableName, $default);
    }

    public static function beginTransaction() {
        return self::beginTransactionHandler();
    }

    public static function commit() {
        return self::commitHandler();
    }

    public static function rollback() {
        return self::rollbackHandler();
    }

    public static function executeQuery($sql, $where = []) {
        return self::executeQueryHandler($sql, $where);
    }

    public static function buildSetClause(array $data) {
        return self::buildSetClauseHandler($data);
    }

    public static function buildWhereClause($where) {
        return self::buildWhereClauseHandler($where);
    }

    public static function jsonEncode($value) {
        return self::jsonEncodeHandler($value);
    }

    public static function countRows($table, $where = []) {
        return self::countRowsHandler($table, $where);
    }

    public static function selectSingle($table, $where = [], $select = '*', $returnObject = true) {
        return self::selectSingleHandler($table, $where, $select, $returnObject);
    }

    public static function selectMultiple($table, $where = [], $select = '*', $returnObject = true) {
        return self::selectMultipleHandler($table, $where, $select, $returnObject);
    }

    public static function insert($table, $data, $created_at_data = true) {
        return self::insertHandler($table, $data, $created_at_data);
    }

    public static function insertMultiple($table, $dataList, $lastInsert = false) {
        return self::insertMultipleHandler($table, $dataList, $lastInsert);
    }

    public static function update($table, $data, $where = []) {
        return self::updateHandler($table, $data, $where);
    }

    public static function updateMultiple($table, $dataList, $whereList) {
        return self::updateMultipleHandler($table, $dataList, $whereList);
    }

    public static function delete($table, $where = []) {
        return self::deleteHandler($table, $where);
    }

    public static function deleteMultiple($table, $idList, $whereList) {
        return self::deleteMultipleHandler($table, $idList, $whereList);
    }

    public static function accInsertBulkData($table, $dump_data_array, $dumpType) {
        return self::accInsertBulkDataHandler($table, $dump_data_array, $dumpType);
    }
}

?>