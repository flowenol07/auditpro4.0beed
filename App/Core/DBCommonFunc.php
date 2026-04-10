<?php

namespace Core;

class DBCommonFunc {

    public static function yearMasterData($model, $filters = ['where' => null, 'params' => []] )
	{
		if(!is_object($model))
			return null;

		return $model -> getAllYears($filters, 'sql', 'SELECT id, year AS fyear, CONCAT(year, " - " , year + 1) AS year FROM '  . $model -> getTableName());
	}

	public static function getAllAuditUnitData($model, $filters = ['where' => null, 'params' => []] )
	{
		if(!is_object($model))
			return null;

		return $model -> getAllAuditUnit($filters, 'sql', 'SELECT id, name, audit_unit_code, CONCAT(name, " - ( BR. " , audit_unit_code, " )") AS combined_name, section_type_id FROM '  . $model -> getTableName());
	}

	public static function getAllAuditAssesment($model, $filters = ['where' => null, 'params' => []], $select = '' )
	{
		if(!is_object($model))
			return null;

		if($select != '')
			$select = $select . ' , CONCAT(assesment_period_from, " - ", assesment_period_to, " <span class=\'d-inline-block\'>( ", frequency, " Months Frq. ) </span>") AS combined_period';
		else
			$select = ' id, year_id, audit_unit_id, frequency, assesment_period_from, assesment_period_to, CONCAT(assesment_period_from, " - ", assesment_period_to, " ( ", frequency, " Months Frq. )") AS combined_period, deleted_at';

		return $model -> getAllAuditAssesment($filters, 'sql', 'SELECT '. $select .' FROM '  . $model -> getTableName());
	}

	public static function getAllRiskWeightage($model, $fyId)
	{
		// find audit section model
        $query = "SELECT id, risk_category, (SELECT risk_weight FROM risk_category_weights WHERE risk_category_id = rcm.id AND year_id = '". $fyId ."' AND is_active = 1 AND deleted_at IS NULL LIMIT 1) as risk_weightage FROM risk_category_master rcm WHERE rcm.is_active = 1 AND rcm.deleted_at IS NULL order by rcm.id asc";

		$findRiskCategory = $model -> getAllRiskCategory( [], 'sql', $query );
		return generate_data_assoc_array($findRiskCategory, 'id');        
	}

	public static function getAllEmployeeData($model, $filters = ['where' => null, 'params' => []] )
	{
		if(!is_object($model))
			return null;

		return $model -> getAllEmployees($filters, 'sql', 'SELECT id, name, emp_code, CONCAT(name, " - ( EMP : " , emp_code , " )" ) AS combined_name  FROM '  . $model -> getTableName());
	}

	public static function getAllSchemeData($model, $filters = ['where' => null, 'params' => []] )
	{
		if(!is_object($model))
			return null;

		return $model -> getAllSchemes($filters, 'sql', 'SELECT id, name, scheme_code, CONCAT(name, " - ( SC Code : " , scheme_code , " )" ) AS combined_name  FROM '  . $model -> getTableName());
	}
}

?>