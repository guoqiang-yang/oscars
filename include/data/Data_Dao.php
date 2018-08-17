<?php

/**
 * Database Access Object
 */
class Data_Dao
{
	//表配置
	private $_table = '';
	private $_tableConf = array();
	private $_pk = '';  //主键
	private $_createdTime = '';
	private $_modifiedTime = '';
	private $_statusField = '';

	//某次查询的中间变量
	private $_where = false;
	private $_order = '';
	private $_fields = false;
	private $_limitFrom = 0;
	private $_limitNum = 0;

	//资源句柄
	private $_one;

	public function __construct($table)
	{
		//表配置
		$table = trim($table);
		assert( !empty($table) );

		$this->_table = $table;
		$tableConf = $this->_tableConf = Conf_Dao::get($table);
		$this->_pk = isset($tableConf['pk']) ? $tableConf['pk']:null;
		$this->_createdTime = isset($tableConf['created_time']) ? $tableConf['created_time']:null;
		$this->_modifiedTime = isset($tableConf['modified_time']) ? $tableConf['modified_time']:null;
		$this->_statusField = isset($tableConf['status']) ? $tableConf['status']:null;

		//database 资源
		$this->_one = Data_One::getInstance();
	}

	/**
	 * 设置查询的字段
	 *
	 * @param array $fields 字段
	 * @return $this
	 */
	public function setFields($fields)
	{
		assert(!empty($fields));
		$this->_fields = $fields;
		return $this;
	}

	public function setSlave()
	{
		$this->_one->setSlaveDB();
		return $this;
	}

	/**
	 * 清除查询的中间变量。
	 * 除 $_where 之外，$_where可以复用，因为$_where只是复用在sum,total操作上。
	 */
	private function _clearSelectConf()
	{
		$this->_fields = false;   //fields只能每次用之前设置，以防误用
		$this->_order = '';
		$this->_limitFrom = $this->_limitNum = 0;
		$this->_one->setDefaultDB();
	}

	/**
	 * 根据表主键获取单个数据
	 *
	 * @param $pkValue 主键值
	 * @return array
	 */
	public function get($pkValue, $useCache=true)
	{
		$pkValue = trim($pkValue);
		assert( !empty($pkValue) );

        
        if ($useCache)
        {
            $record = Tool_Cache::get($this->_table, $pkValue);
            if (empty($record))
            {
                //查询数据
                $record = $this->_get($pkValue);

                //设置缓存
                Tool_Cache::set($this->_table, $pkValue, $record);
            }
        }
        else
        {
            //查询数据
            $record = $this->_get($pkValue);
        }
        
		//清理环境
		$this->_clearSelectConf();

		return $record;
	}
    
    // 主键查询
    private function _get($pkValue)
    {
        //查询数据
        $where = array($this->_pk => $pkValue);
        $ret = $this->_one->select($this->_table, array('*'), $where);
        $record = !empty($ret['data']) ? $ret['data'][0]:array();
        
        return $record;
    }
    
	/**
	 * 根据表主键获取多个数据
	 *
	 * @param array $pkValues 主键值数组
	 * @return array
	 */
	public function getList(array $pkValues)
	{
		$pkValues = array_filter($pkValues);
		if( empty($pkValues) ) {
			$this->_where = false;
			return array();
		}

		// 查询结果
		$this->_where = array($this->_pk => $pkValues);
		$fields = $this->_getFields();
		$ret = $this->_one->select($this->_table, $fields, $this->_where, $this->_order);
		$records = !empty($ret['data']) ? $ret['data']:array();
        $records = Tool_Array::list2Map($records, $this->_pk);

		// 清理环境
		$this->_clearSelectConf();
		//Tool_Cache::setBulk($this->_table, $records);
		return $records;
	}

	/**
	 * 根据where条件获取多个数据
	 *
	 * @param mixed $where 查询条件
	 * @param boolean $withPK 返回结果是否以主键为key
	 * @return array
	 */
	public function getListWhere($where, $withPK=true)
	{
		// 格式化条件
		assert(is_array($where) || is_string($where));
		assert(!empty($where));
		$this->_where = $where;

		// 查询
		$fields = $this->_getFields();
		$ret = $this->_one->select($this->_table, $fields, $this->_where,
								$this->_order, $this->_limitFrom, $this->_limitNum);
		$records = !empty($ret['data']) ? $ret['data']:array();

		// 格式化结果
		if (!empty($this->_pk) && $withPK)
		{
			$records = Tool_Array::list2Map($records, $this->_pk);
		}

		// 清理环境
		$this->_clearSelectConf();

		// 返回
		return $records;
	}

	/**
	 * 根据where条件获取多个数据。
	 *
	 * @param mixed $where 查询条件
	 * @return array
	 */
	public function getMapWhere($where)
	{
		// 格式化条件
		if (is_string($where) && func_num_args() > 1)
		{
			$params = array_slice(func_get_args(), 1);
			$where = vsprintf($where, $params);
		}

		return $this->getListWhere($where, true);
	}

	/**
	 * 获取所有数据。数据量大的表慎用。
	 *
	 * @return array
	 */
	public function getAll()
	{
		// 查询
		$fields = $this->_getFields();
		$where = array();
		$ret = $this->_one->select($this->_table, $fields, $where, $this->_order);
		$records = !empty($ret['data']) ? $ret['data']:array();

		// 格式化结果
		if (!empty($this->_pk))
		{
			$records = Tool_Array::list2Map($records, $this->_pk);
		}

		// 清理环境
		$this->_clearSelectConf();

		// 返回
		return $records;
	}

	/**
	 * 获取查询条件下的所有条数
	 *
	 * @param $where
     * @param $field
	 * @return bool|int
	 */
	public function getTotal($where = false, $field = '1')
	{
		// 格式化条件
		if (!empty($where))
		{
			assert(is_array($where) || is_string($where));
			if (is_string($where) && func_num_args() > 2)
			{
				$params = array_slice(func_get_args(), 2);
				$where = vsprintf($where, $params);
			}
		}
		else
		{
			assert(!empty($this->_where));
			$where = $this->_where;
		}

		//查询
		$fields = array('count(' . $field . ')');
		$ret = $this->_one->select($this->_table, $fields, $where);
		$total = intval($ret['data'][0]['count(' . $field . ')']);

		// 清理环境
		$this->_clearSelectConf();

		return $total;
	}

	/**
	 * 获取汇总
	 *
	 * @param $field
	 * @param bool $where
	 * @return int
	 */
	public function getSum($field, $where=false)
	{
		$field = trim($field);
		assert( !empty($field) );

		// 格式化条件
		if (!empty($where))
		{
			assert(is_array($where) || is_string($where));
			if (is_string($where) && func_num_args() > 2)
			{
				$params = array_slice(func_get_args(), 2);
				$where = vsprintf($where, $params);
			}
		}
		else
		{
			assert(!empty($this->_where));
			$where = $this->_where;
		}

		//查询
		$field = 'sum(' . mysql_escape_string($field). ')';
		$data = $this->_one->select($this->_table, array($field), $where);
		$sum = intval($data['data'][0][$field]);

		// 清理环境
		$this->_clearSelectConf();

		return $sum;
	}

	/**
	 * 设置查询排序规则
	 *
	 * @param string $order
	 * @param string $sc
	 * @return $this
	 */
	public function order($order, $sc='asc')
	{
		$order = trim($order);
		if (!empty($order) && strncasecmp($order, 'order by', 8)!=0)
		{
			$order = 'order by '.$order;
            $this->_order = $order . ' ' . $sc;
		}
        else
        {
            $this->_order = $order;
        }
        
		return $this;
	}

	/**
	 * 设置查询区段
	 *
	 * @param int $from 起始位置
	 * @param int $num 查询条数
	 * @return $this
	 */
	public function limit($from, $num)
	{
		$this->_limitFrom = $from;
		$this->_limitNum = $num;
		return $this;
	}

	/**
	 * 按照主键删除记录
	 *
	 * @param $pkValue
     * @param $phyDel 是否为物理删除
	 * @return bool
	 */
	public function delete($pkValue, $phyDel=false)
	{
		$pkValue = trim($pkValue);
		assert( !empty($pkValue) );

		$where = array($this->_pk => $pkValue);

        if ($phyDel)
        {
            $ret = $this->_one->setDefaultDB()->delete($this->_table, $where);
        }
        else
        {
            if ($this->_statusField)
            {
                $arr = array($this->_statusField => Conf_Base::STATUS_DELETED);
                $ret = $this->_one->setDefaultDB()->update($this->_table, $arr, array(), $where);
            }else
            {
                $ret = $this->_one->setDefaultDB()->delete($this->_table, $where);
            }
        }
		Tool_Cache::delete($this->_table, $pkValue);
		return $ret['affectedrows'] ? true:false;
	}

	/**
	 * 根据where条件删除数据
	 *
	 * @param $where 查询条件
	 * @return array
	 */
	public function deleteWhere($where, $phyDel=false)
	{
		// 格式化条件
		assert(is_array($where) || is_string($where));
		assert(!empty($where));
		if (is_string($where) && func_num_args() > 1)
		{
			$params = array_slice(func_get_args(), 1);
			$where = vsprintf($where, $params);
		}

		// 删除
        if ($phyDel)
        {
            $ret = $this->_one->setDefaultDB()->delete($this->_table, $where);
        }
        else
        {
            if ($this->_statusField)
            {
                $arr = array($this->_statusField => Conf_Base::STATUS_DELETED);
                $ret = $this->_one->setDefaultDB()->update($this->_table, $arr, array(), $where);
            }else
            {
                $ret = $this->_one->setDefaultDB()->delete($this->_table, $where);
            }
        }
        
		return $ret['affectedrows'] ? true:false;
	}

	/**
	 * 按照主键更新记录
	 *
	 * @param $key
	 * @param array $info
	 * @param array $change
	 * @return int 实际更新的条数
	 */
	public function update($key, array $info, array $change=array())
	{
		$key = trim($key);
		assert( !empty($key) );
		assert( !empty($info) || !empty($change));

		//补充时间字段
		if ($this->_modifiedTime && !isset($info[$this->_modifiedTime]))
		{
			$date = date('Y-m-d H:i:s');
			$info[$this->_modifiedTime] = $date;
		}

		//执行更新
		$where = array($this->_pk => $key);
		$ret = $this->_one->setDefaultDB()->update($this->_table, $info, $change, $where);

		//清理环境
		Tool_Cache::delete($this->_table, $key);

		return $ret['affectedrows'];
	}

	/**
	 * 按照主键更新记录
	 *
	 * @param $where
	 * @param array $info
	 * @param array $change
	 * @return int 实际更新的条数
	 */
	public function updateWhere($where, array $info, array $change=array())
	{
		assert( !empty($info) || !empty($change));

		// 格式化条件
		assert(is_array($where) || is_string($where));
		assert(!empty($where));
		if (is_string($where) && func_num_args() > 3)
		{
			$params = array_slice(func_get_args(), 3);
			$where = vsprintf($where, $params);
		}
        
		//补充时间字段
		if ($this->_modifiedTime && !isset($info[$this->_modifiedTime]))
		{
			$date = date('Y-m-d H:i:s');
			$info[$this->_modifiedTime] = $date;
		}

		//执行更新
		$ret = $this->_one->setDefaultDB()->update($this->_table, $info, $change, $where);
		return $ret['affectedrows'];
	}

	/**
	 * 插入数据
	 *
	 * @param $info
	 * @param array $updateFields 如果数据已经存在，则更新
	 * @param array $changeFields 如果数据已经存在，则更新
	 * @return mixed
	 */
	public function add($info, $updateFields= array(), $changeFields= array())
	{
		assert( !empty($info) );

		//补充时间字段
		$date = date('Y-m-d H:i:s');
		if ($this->_createdTime && !isset($info[$this->_createdTime]))
		{
			$info[$this->_createdTime] = $date;
		}
		if ($this->_modifiedTime && !isset($info[$this->_modifiedTime]))
		{
			$info[$this->_modifiedTime] = $date;
			$updateFields[] = $this->_modifiedTime;
		}

		//执行插入
		$ret = $this->_one->setDefaultDB()->insert($this->_table, $info, $updateFields, $changeFields);
		return $ret['insertid'];
	}

    /**
     * 批量插入数据
     *
     * @param array $updateFields 如果数据已经存在，则更新
     * @return mixed
     */
    public function batchAdd($insertField)
    {
        assert(!empty($insertField));

        //执行插入
        $ret = $this->_one->setDefaultDB()->batchInsert($this->_table, $insertField);
        return $ret['insertid'];
    }

	/**
	 * 设置数据。 空则插入，有则更新
	 * 注意：必须设置表的主键
	 *
	 * @param string $key
	 * @param array $values 如果数据已经存在，则更新
	 * @return int
	 */
	public function set($key, $values)
	{
		assert(!empty($this->_pk));

		$updateFields = array_keys($values);
		$values[$this->_pk] = $key;
		return $this->add($values, $updateFields);
	}

	private function _getFields()
	{
		$fields = !empty($this->_fields) ? $this->_fields:$this->_tableConf['fields'];
		if (empty($fields))
		{
			$fields = array('*');
		}
		return $fields;
	}
}
