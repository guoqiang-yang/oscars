<?php
/**
 * sql语句拼装
 */

class Tool_Sql
{
	/**
	 *  1.数据未插入时，插入数据
	 *  2.数据已经插入时，更新数据
	 */
	function insert($table, $insertfield , $updateField = array(), $changeField = array(), $notEscFields=array())
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
			$v = (empty($notEscFields) || !in_array($k,$notEscFields)) ? ("'".mysql_escape_string($v)."'") : $v;
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
				$v = (empty($notEscFields) || !in_array($k,$notEscFields)) ? ("'".mysql_escape_string($insertfield[$k])."'") : $insertfield[$k];
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
					$sql .= ($flag==0?"":", ").$k."=CONCAT(".$k.",'".mysql_escape_string($v)."')";
				}
				$flag = 1;
			}
		 }
		 return $sql;
	}

	/**
	 * 插入多条数据
	 */
	function batchInsert($table , $insertFieldList, $notEscFields=array())
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
				$v = (empty($notEscFields) || !in_array($k,$notEscFields)) ? ("'".mysql_escape_string($v)."'") : $v;
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
	function delete($table , $whereField)
	{
		$where = "";
		foreach($whereField as $k=>$v)
		{
			if(0 != strlen($where))
			{
				$where .= " and ";
			}
			$where .= $k." = '".mysql_escape_string($v)."'";
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
	function replace($table , $replaceField)
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
			$sql .= ($flag==0?"'":", '").mysql_escape_string($v)."'";
			$flag = 1;
		}
		$sql .= ")";
		return $sql;
	}

	/*
	 * replace多条数据
	 */
	function replaceEx($table , $replaceFieldList)
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
				$sql .= ($flag==0?"'":", '").mysql_escape_string($v)."'";
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
	function update($table , $updateField , $changeField = array() , $whereField, $notEscFields=array())
	{
		$sql = "update $table set ";
		$flag = 0;
		foreach($updateField as $k => $v)
		{
			$v = (empty($notEscFields) || !in_array($k,$notEscFields)) ? ("'".mysql_escape_string($v)."'") : $v;
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
						$k, $k, mysql_escape_string($v));
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
						$v[$i] = mysql_escape_string($item);
					}
					$where .= $k." in ('". implode("', '", $v) ."')";
				}
				else
				{
					$where .= $k." = '".mysql_escape_string($v)."'";
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
	function updateEx($table , $updateField , $changeField = array() , $where, $notEscFields=array())
	{
		$sql = "update $table set ";
		$flag = 0;
		foreach($updateField as $k => $v)
		{
			$v = (empty($notEscFields) || !in_array($k,$notEscFields)) ? ("'".mysql_escape_string($v)."'") : $v;
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
						$k, $k, mysql_escape_string($v));
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
    function select($table , $selectField , $whereField , $order , $start , $num , $foundRows=false)
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
						$v[$i] = mysql_escape_string($item);
					}
					$where .= $k." in ('". implode("', '", $v) ."')";
				}
				else
				{
					$where .= $k." = '".mysql_escape_string($v)."'";
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
    function selectRawWhere($table , $selectField , $where , $order , $start , $num , $foundRows=false)
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