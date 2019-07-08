<?php
/* Copyright (C) 2019-... LiveMediaGroup - Marko Popovic <marko.popovic@livemediagroup.de>
 * Copyright (C) 2019-... LiveMediaGroup - Milos Petkovic <milos.petkovic@livemediagroup.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class ElbCommonManager
{
	/**
	 * Function retrieves and create objects from query. 
	 * All columns returned in result are assigned to object properties
	 * 
	 * @param resource $res	database query result
	 * @param string $name	name of the object to create
	 * @param boolean $set_id set id of object
	 * @param boolean $force_prop set property on object event not exists
	 * @return unknown
	 */
	static function resultToObject($res, $name, $set_id=true, $force_prop=false)
    {
		global $db;
		$obj=new $name($db);
		$vars=get_object_vars($res);
		foreach($vars as $var => $value) {
			if(property_exists($obj, $var) || $force_prop) {
				$obj->$var=$value;
			}
		}
		if($set_id) {
			$obj->id=$res->rowid;
		}
		return $obj;
	}
	
	/**
	 * Function returns list of objects from database query
	 * 
	 * @param string $sql	query to execute
	 * @param string $name	name of object to retrieve, if empty stdClass is created
	 * @param boolean $force_prop set property on object event not exists
	 * @return array |boolean	returns array of objects, or false on error
	 */
	static function queryList($sql, $name='', $force_prop = false)
    {
		global $db;
		
		$list=array();
		$result=$db->query($sql);
		if($result) {
		
			$num=$db->num_rows($result);
			$i = 0;
			while($i<$num) {
				$objp = $db->fetch_object($result);
				if(!empty($name)) {
					$list[]=ElbCommonManager::resultToObject($objp, $name, true, $force_prop);
				} else {
					$list[]=$objp;
				}
				$i++;
			}
				
			return $list;
		
		} else {
			dol_print_error($db);
			return false;
		}
	}
	
	/**
	 * Function creates and executes insert of record in database
	 * 
	 * @param object $obj	object to insert
	 * @param array $fields	list of fields name from object that will be inserted
	 * @param boolean $transaction set true to use trnasaction in method
	 * @return unknown|number id of inserted object, -1 if there is error
	 */
	static function insert(&$obj,$fields,$transaction=true)
    {
		global $db;
		
		if(!method_exists($obj, 'getTableName')) {
			dol_print_error($db,"For insert function entity class ".get_class($obj)." must implement 'getTableName' method");
			return -1;
		}
		
		$tbl_name=$obj->getTableName();
		
		if($transaction) $db->begin();
		
		$sql = "INSERT INTO ".MAIN_DB_PREFIX.$tbl_name." (";
		
		$fields_num=count($fields);
		for($i=0;$i<$fields_num;$i++) {
			$field = $fields[$i];
			$sql.=" ".$field." ";
			if($i<$fields_num-1) $sql.=" , ";
		}
		$sql.= ") VALUES (";
		for($i=0;$i<$fields_num;$i++) {
			$field = $fields[$i];
			$val = $obj->$field;
			if(is_null($val)) {
				$sql.="null";
			} else {
				$sql.=" '".$db->escape($val)."' ";
			}
			if($i<$fields_num-1) $sql.=" , ";
		}
		$sql.= ")";
		
		dol_syslog(get_class($obj)."::create sql=".$sql);
		
		$result = $db->query($sql);
			
		if ($result)
		{
			$obj->id = $db->last_insert_id(MAIN_DB_PREFIX.$tbl_name);
			if($transaction) $db->commit();
			dol_syslog(get_class($obj)."::create done id=".$obj->id);
			return $obj->id;
		}
		else
		{
			if($transaction) $db->rollback();
			$obj->error=$db->lasterror();
			return -1;
		}
	}
	
	/**
	 * Function executes sql query
	 * 
	 * 
	 * @param string $sql	query to execute
	 * @param boolean $transaction	if query will be executed in transaction, default: true
	 * @return number|boolean	1 if success, false otherwise
	 */
	static function execute($sql,$transaction=true)
    {
		global $db;
		
		if($transaction) $db->begin();
		dol_syslog(get_called_class()."::execute sql=".$sql);
		
		$result = $db->query($sql);
			
		if ($result) {
			if($transaction) $db->commit();
			return true;
		} else {
			if($transaction) $db->rollback();
			dol_print_error($db);
			return false;
		}
	}
	
	/**
	 * Function returns first object from database query
	 *
	 * @param string $sql	query to execute
	 * @param string $name	name of object to retrieve, if empty stdClass is created
	 * @return object, or false on error
	 */
	static function querySingle($sql, $name='', $force_prop = false)
    {
		global $db;
	
		$result=$db->query($sql);
		if($result) {
			$num=$db->num_rows($result);
			if($num>0){
				$objp = $db->fetch_object($result);
				if(!empty($name)) {
					return ElbCommonManager::resultToObject($objp, $name, true, $force_prop);
				} else {
					return $objp;
				}
			} else {
				return null;
			}
		} else {
			dol_print_error($db);
			return false;
		}
	}
	
	/**
	 * Function updates record in database
	 * 
	 * @param object $obj	object which values need to be updated
	 * @param array $fields array of fields name to be updated
	 * @param boolean $transactionset true to use trnasaction in update
	 * @return number|boolean
	 */
	static function update($obj,$fields,$transaction=true)
    {
		global $db;
		
		if(!method_exists($obj, 'getTableName')) {
			dol_print_error($db,"For insert function entity class ".get_class($obj)." must implement 'getTableName' method");
			return -1;
		}
			
		dol_syslog(get_class($obj)."::update id=".$obj->id, LOG_DEBUG);
	
		if($transaction) $db->begin();
	
		$sql = "UPDATE ".MAIN_DB_PREFIX.$obj->getTableName();
		$sql.= " SET ";
		
		$fields_num=count($fields);
		for($i=0;$i<$fields_num;$i++) {
			$field = $fields[$i];
			$val = $obj->$field;
			if (is_null($val) || $val === '')
				$sql.=" ".$field."=NULL";
			else 
				$sql.=" ".$field."='".$db->escape($val)."'";
			if($i<$fields_num-1) $sql.=" , ";
		}
		$sql.= " WHERE rowid = " . $obj->id;
	
		dol_syslog(get_called_class()." update sql=".$sql);
	
		$resql=$db->query($sql);
	
		if ($resql)
		{
			if($transaction) $db->commit();
			return true;
		}
		else
		{
			$error=$db->error()." - ".$sql;
			if($transaction) $db->rollback();
			dol_print_error($db,$error);
			return false;
		}
	}
	
	/**
	 * Function updates field in one row in table
	 * 
	 * @param string $tbl_name name of table to update
	 * @param string $field - name of field to update
	 * @param unknown $value - value to update
	 * @param int $rowid - id of row to update
	 * @return Ambigous <number, boolean> 1 if ok, false otherwise
	 */
	static function updateField($tbl_name,$field,$value,$rowid,$key_column="rowid")
    {
		global $db;
		$sql="UPDATE ".MAIN_DB_PREFIX.$tbl_name;
		if (is_null($value)) {
            $sql .= " SET " . $field . " = null";
        } else {
            $sql .= " SET " . $field . "='" . $db->escape($value) . "'";
        }
		$sql.=" WHERE $key_column=".$db->escape($rowid);
		return self::execute($sql);
	}
	
	static function deleteRow($tbl_name,$rowid)
    {
		global $db;
		$sql="DELETE FROM ".MAIN_DB_PREFIX.$tbl_name;
		$sql.=" WHERE rowid=".$db->escape($rowid);
		return self::execute($sql);
	}
	
	static function fetch($tbl_name, $id, $name, $key_column='rowid')
    {
		global $db;
		$sql="SELECT * FROM ".MAIN_DB_PREFIX.$tbl_name;
		$sql.=" WHERE ".$key_column."=".$db->escape($id);
		return self::querySingle($sql, $name);
	}
	
	static function fetchField($tbl_name, $field, $rowid)
    {
		global $db;
		$sql="SELECT $field FROM ".MAIN_DB_PREFIX.$tbl_name;
		$sql.=" WHERE rowid=".$db->escape($rowid);
		$obj = self::querySingle($sql);
		return $obj->$field;
	}

	static function fetchFieldByField($tbl_name, $getfield, $search_column_name, $search_column_value)
    {
		global $db;
		$sql="SELECT $getfield FROM ".MAIN_DB_PREFIX.$tbl_name;
		$sql.=" WHERE ".$search_column_name." ='".$db->escape($search_column_value)."'";
		$obj = self::querySingle($sql);
		return $obj->$getfield;
	}

    static function fetchFieldValuesByWildcard($tbl_name, $getfield, $search_column_name, $search_column_value)
    {
        global $db;
        $sql="SELECT $getfield FROM ".MAIN_DB_PREFIX.$tbl_name;
        $sql.=" WHERE ".$search_column_name." LIKE '%".$db->escape($search_column_value)."%'";
        return self::queryList($sql);
    }
	
}