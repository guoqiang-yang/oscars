<?php

/**
 * 数据库操作
 */

class Data_DB
{
	const MASTER = 'master',
		  SLAVE = 'slave';

	/* Class Attr */
	private static $_prx = NULL;
	private static $_db = NULL;

	private function __construct()
	{
	}

	public static function getInstance()
	{
		if (empty(self::$_prx))
		{
			self::$_prx = new Data_DB();
		}
		return self::$_prx;
	}

	public static function free()
	{
        if (!empty(self::$_db))
        {
            foreach (self::$_db as $db)
            {
                if (!empty($db))
                {
                    $db->close();
                }
            }
        }
        
		self::$_db = null;
	}

	private function _getDbInfo()
	{
		$info = array(DB_HOST, DB_USER, DB_PASS, DB_NAME);

		return $info;
	}

	private function _getConnection()
	{
		if (empty(self::$_db))
		{
			list($host, $user, $pass, $database) = self::_getDbInfo();

			self::$_db = new mysqli($host, $user, $pass, $database);
			if (!self::$_db)
			{
				throw new Exception("Could not connect \'$host\' - " . mysqli_connect_error() );
			}
			if (!self::$_db->query("set names 'utf8';"))//TODO:	用配置取代
			{
				throw new Exception("Set Names 'utf8' Error	- "	.
					self::$_db->errno .":". self::$_db->error, self::$_db->errno );
			}
		}
		return self::$_db;
	}

	public function setSlaveDB()
	{
	}

	public function setDefaultDB()
	{
	}

	/**
	 * 单条sql查询
	 * @param cacheTime: <0	- 强制更新LCache，读主库;
	 *					  0	- 不缓存到LCache;
	 *					 >0	- 缓存到LCache，时长为$cacheTime
	 * @return Data_Result
	 * @throws Exception
	 */
	public function	sQuery($kind , $hintId , $sql)
	{
		$db = self::_getConnection();
		$res = $db->query($sql);
		if (false === $res)
		{
			throw new Exception("excute '$sql' error - " . $db->errno .":". $db->error, $db->errno );
		}

		$data = array();
		$rownum = 0;
		if (is_object($res)	&& $res->num_rows != 0)
		{
			while ($row = $res->fetch_array(MYSQLI_ASSOC))
			{
				$data[] = $row;
			}
			$rownum = $res->num_rows;
		}

		return new Data_Result($data, $rownum, $db->insert_id, $db->affected_rows);
	}

	/**
	 * 多条sql查询
	 */
	public function	mQuery($kind , $hintId , $sqls)
	{
		$db = self::_getConnection();
		$sqls = implode(';', $sqls);
		$res = $db->multi_query($sqls);
		if (!$res)
		{
			throw new Exception("excute '$sqls'	error - " . $db->errno .":". $db->error, $db->errno	);
		}

		$result = array();
		do {
			$data = array();
			$rownum = 0;
			$resSet = $db->store_result();
			if (is_object($resSet) && $resSet->num_rows>0)
			{
				while ($row = $resSet->fetch_array(MYSQLI_ASSOC))
				{
					$data[] = $row;
				}
				$rownum = $resSet->num_rows;
				$resSet->close();
			}
			else if	($db->errno)
			{
				throw new Exception("store result for '$sqls' error - "	. $db->errno .":". $db->error, $db->errno );
			}
			$result[] = new	Data_Result($data, $rownum,	$db->insert_id,	$db->affected_rows);
		} while	($db->next_result());

		return $result;
	}
}
