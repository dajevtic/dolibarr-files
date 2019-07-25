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
	 * @param object    $res	    Database query result
	 * @param string    $name	    Name of the object to create
	 * @param boolean   $set_id     Set id of object
	 * @param boolean   $force_prop Set property on object if object's property exists
	 * @return object
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
     * Function executes sql query
     *
     * @param   string      $sql            Query to execute
     * @param   boolean     $transaction    Flag if query will be executed in transaction
     * @return  boolean     true if success, false otherwise
     * @throws Exception
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
	 * @param   string      $sql	        Query to execute
	 * @param   string      $name	        Name of object to retrieve, if empty stdClass is created
	 * @param   boolean     $force_prop	    Flag if object's properties should be populated
	 * @return  object|boolean|null
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
     * @param   object      $obj            Object which values need to be updated
     * @param   array       $fields         Array of fields name to be updated
     * @param   boolean     $transaction    Set true to use database transaction in update method
     * @return  number|boolean
     * @throws  Exception
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
	
		if ($resql)	{
			if($transaction) $db->commit();
			return true;
		} else {
			$error=$db->error()." - ".$sql;
			if($transaction) $db->rollback();
			dol_print_error($db,$error);
			return false;
		}
	}

    /**
     * Function updates field in one row in table
     *
     * @param string $tbl_name Name of table to update
     * @param string $field Name of field to update
     * @param mixed $value Value to update
     * @param int $rowid ID of row to update
     * @param string $key_column Name of ID column
     * @return  boolean     true if ok, false otherwise
     * @throws Exception
     */
	static function updateField($tbl_name,  $field, $value, $rowid, $key_column="rowid")
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

    /**
     * Function fetches value of field from a database record
     *
     * @param   string  $tbl_name   Database table name
     * @param   string  $field      Name of column which we need value from
     * @param   int     $rowid      ID, primary key, of the table
     * @return  mixed
     */
	static function fetchField($tbl_name, $field, $rowid)
    {
		global $db;
		$sql="SELECT $field FROM ".MAIN_DB_PREFIX.$tbl_name;
		$sql.=" WHERE rowid=".$db->escape($rowid);
		$obj = self::querySingle($sql);
		return $obj->$field;
	}

}