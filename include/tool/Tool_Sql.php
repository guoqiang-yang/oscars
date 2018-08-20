<?php
/**
 * sql语句拼装
 * 
 * @Notice - TODO
 * 
 *      【有一个大坑】：因为php7 删除了mysql_* 方法的支持，暂定使用 addslashes 替代 mysql_escape_string
 *                   addslashes 现在不安全
 * 
 */

class Tool_Sql
{
	/**
	 *  1.数据未插入时，插入数据
	 *  2.数据已经插入时，更新数据
	 */
	public static function insert($table, $insertfield , $updateField = array(), $changeField = array(), $notEscFields=array())
	{
		$sql = "insert into $table (";

		$flag = 0;
		foreach($insertfield as $k => $v)
		{
			$sql .= ($flag==0?"":", ").$k;
			$flag = 1;
		}

		$sql .= ") values(";
		$flag = 0;
		foreach($insertfield as $k => $v)
		{
			$v = (empty($notEscFields) || !in_array($k,$notEscFields)) ? ("'".addslashes($v)."'") : $v;
			$sql .= ($flag==0?"":", "). $v;
			$flag = 1;
		}
		$sql .= ")";

		if (!empty($updateField) || !empty($changeField))
		{
			$sql .= " ON DUPLICATE KEY UPDATE ";
			$flag = 0;
			foreach ($updateField as $k)
			{
				$v = (empty($notEscFields) || !in_array($k,$notEscFields)) ? ("'".addslashes($insertfield[$k])."'") : $insertfield[$k];
				$sql .= ($flag==0?"":", ").$k." = ".$v;
				$flag = 1;
			}

			foreach ($changeField as $k => $v)
			{
				if (is_numeric($v))
				{
					$sql .= ($flag==0?"":", ").$k."=(".$k.($v >= 0 ? "+" : "").($v).")";
				}
				else
				{
					$sql .= ($flag==0?"":", ").$k."=CONCAT(".$k.",'".addslashes($v)."')";
				}
				$flag = 1;
			}
		 }
		 return $sql;
	}

	/**
	 * 插入多条数据
	 */
	public static function batchInsert($table , $insertFieldList, $notEscFields=array())
	{
		$tmpArr = $insertFieldList[0];

		$sql = "insert into $table (";
		$flag = 0;
		foreach($tmpArr as $k => $v)
		{
			$sql .= ($flag==0?"":", ").$k;
			$flag = 1;
		}
		$sql .= ") values";
		foreach ($insertFieldList as $varr)
		{
			$flag = 0;
			$sql.="(";
			foreach($varr as $k => $v)
			{
				$v = (empty($notEscFields) || !in_array($k,$notEscFields)) ? ("'".addslashes($v)."'") : $v;
				$sql .= ($flag==0?"":", ").$v;
				$flag = 1;
			}
			$sql .= "),";
		}
		$sql = trim($sql,' ,');

		return $sql;
	}

	/**
	 * 删除
	 */
	public static function delete($table , $whereField)
	{
		$where = "";
		foreach($whereField as $k=>$v)
		{
			if(0 != strlen($where))
			{
				$where .= " and ";
			}
			$where .= $k." = '".addslashes($v)."'";
		}

		if(0 == strlen($where))
		{   //没有限制条件，不能删除，需手工终端操作，避免灾难
			return "";
		}

		$sql = "delete from ".$table." where ".$where;
		return $sql;
	}

	/**
	 * replace
	 */
	public static function replace($table , $replaceField)
	{
		$sql = "replace into $table (";
		$flag = 0;
		foreach($replaceField as $k => $v)
		{
			$sql .= ($flag==0?"":", ").$k;
			$flag = 1;
		}
		$sql .= ") values(";
		$flag = 0;
		foreach($replaceField as $k => $v)
		{
			$sql .= ($flag==0?"'":", '").addslashes($v)."'";
			$flag = 1;
		}
		$sql .= ")";
		return $sql;
	}

	/*
	 * replace多条数据
	 */
	public static function replaceEx($table , $replaceFieldList)
	{
		$tmpArr = $replaceFieldList[0];
		$sql = "replace into $table (";
		$flag = 0;
		foreach($tmpArr as $k => $v)
		{
			$sql .= ($flag==0?"":", ").$k;
			$flag = 1;
		}
		$sql .= ") values";
		foreach ($replaceFieldList as $varr)
		{
			$flag = 0;
			$sql.="(";
			foreach($varr as $k => $v)
			{
				$sql .= ($flag==0?"'":", '").addslashes($v)."'";
				$flag = 1;
			}
			$sql .= "),";
		}
		$sql = trim($sql,' ,');

		return $sql;
	}

	/**
	 * 更新
	 */
	public static function update($table , $updateField , $changeField = array() , $whereField, $notEscFields=array())
	{
		$sql = "update $table set ";
		$flag = 0;
		foreach($updateField as $k => $v)
		{
			$v = (empty($notEscFields) || !in_array($k,$notEscFields)) ? ("'".addslashes($v)."'") : $v;
			$sql .= ($flag==0?"":", ").$k." = ".$v;
			$flag = 1;
		}
		foreach($changeField as $k => $v)
		{
			if (is_numeric($v))
			{
				$sql .= sprintf("%s %s=(%s%s%s)", ($flag==0 ? "" : ","),
						$k, $k, ($v >= 0 ? "+" : " "), $v);
				$flag = 1;
			}
			else
			{
				$sql .= sprintf("%s %s=CONCAT(%s,'%s')", ($flag==0 ? "" : ","),
						$k, $k, addslashes($v));
				$flag = 1;
			}
		}

		$where = "";
		if (is_array($whereField)|| is_object($whereField))
		{
			foreach($whereField as $k=>$v)
			{
				if(0 != strlen($where))
				{
					$where .= " and ";
				}
				if(is_array($v))
				{
					foreach($v as $i=>$item)
					{
						$v[$i] = addslashes($item);
					}
					$where .= $k." in ('". implode("', '", $v) ."')";
				}
				else
				{
					$where .= $k." = '".addslashes($v)."'";
				}
			}
		} 
		else if(is_string($whereField))
		{
			$where = $whereField;
		}
		
		if(0 == strlen($where))
		{   //没有限制条件，不能批量更新，需手工终端操作，避免灾难
			return "";
		}
		$sql .= " where ".$where;

		return $sql;
	}

	/**
	 * 更新
	 */
	public static function updateEx($table , $updateField , $changeField = array() , $where, $notEscFields=array())
	{
		$sql = "update $table set ";
		$flag = 0;
		foreach($updateField as $k => $v)
		{
			$v = (empty($notEscFields) || !in_array($k,$notEscFields)) ? ("'".addslashes($v)."'") : $v;
			$sql .= ($flag==0?"":", ").$k." = ".$v;
			$flag = 1;
		}
		foreach($changeField as $k => $v)
		{
			if (is_numeric($v))
			{
				$sql .= sprintf("%s %s=%s%s%s", ($flag==0 ? "" : ","),
						$k, $k, ($v >= 0 ? "+" : " "), $v);
				$flag = 1;
			}
			else
			{
				$sql .= sprintf("%s %s=CONCAT(%s,'%s')", ($flag==0 ? "" : ","),
						$k, $k, addslashes($v));
				$flag = 1;
			}
		}

		if(0 != strlen($where))
		{
			$sql .= " where ".$where;
		}
		else
		{   //没有限制条件，不能批量更新，需手工终端操作，避免灾难
			return "";
		}

		return $sql;
	}

	/**
	 * select
	 */
    public static function select($table , $selectField , $whereField , $order , $start , $num , $foundRows=false)
	{
		assert(is_array($whereField));
        
		$select = implode(",",$selectField);

		$where = "";
		if($whereField)
		{
			foreach($whereField as $k => $v)
			{
				if(0 != strlen($where))
				{
					$where .= " and ";
				}
				if(is_array($v))
				{
					foreach($v as $i=>$item)
					{
						$v[$i] = addslashes($item);
					}
					$where .= $k." in ('". implode("', '", $v) ."')";
				}
				else
				{
					$where .= $k." = '".addslashes($v)."'";
				}
			}
		}

		if($foundRows)
		{
			$sql = "select SQL_CALC_FOUND_ROWS ".$select." from ".$table;
		}
		else
		{
			$sql = "select ".$select." from ".$table;
		}

		if(0 != strlen($where))
		{
			$sql .= " where ".$where;
		}
		$sql .= " ".$order;

		if($num != 0)
		{
			$sql .= " limit ".intval($start).", ".intval($num);
		}
		return $sql;
	}


	/**
	 * select
	 */
    public static function selectRawWhere($table , $selectField , $where , $order , $start , $num , $foundRows=false)
	{
		$select = implode(",",$selectField);

		if($foundRows)
		{
			$sql = "select SQL_CALC_FOUND_ROWS ".$select." from ".$table;
		}
		else
		{
			$sql = "select ".$select." from ".$table;
		}

		if(0 != strlen($where))
		{
			$sql .= " where ".$where;
		}
		$sql .= " ".$order;

		if($num != 0)
		{
			$sql .= " limit ".intval($start).", ".intval($num);
		}

		return $sql;
	}
}