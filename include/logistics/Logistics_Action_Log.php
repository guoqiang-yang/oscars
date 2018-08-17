<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/8/20
 * Time: 14:16
 */
class Logistics_Action_Log extends Base_Func
{
	private $_dao;


	public function __construct()
	{
		$this->_dao = new Data_Dao('t_logistics_action_log');

		parent::__construct();
	}

	public function add(array $info)
	{
		return $this->_dao->add($info);
	}

	public function getList($conf, $start = 0, $num = 20)
	{
		$where = $this->_getWhereByConf($conf);

		// 查询数量
		$total = $this->_dao->getTotal($where);
		if ($total <= 0)
		{
			return array('total' => $total, 'list' => array());
		}

		// 查询结果
		$list = $this->_dao->order('id', 'desc')->limit($start, $num)->getListWhere($where);

		return array('total' => $total, 'list' => $list);
	}

	private function _getWhereByConf($searchConf)
	{
		$where = ' 1=1 ';

		if (!empty($searchConf['oid']))
		{
			$where .= sprintf(' AND oid="%d"', $searchConf['oid']);
		}
		if (!empty($searchConf['admin_id']))
		{
			$where .= sprintf(' AND admin_id="%d"', $searchConf['admin_id']);
		}
		if (!empty($searchConf['action_type']))
		{
			$where .= sprintf(' AND action_type="%d"', $searchConf['action_type']);
		}
		if (!empty($searchConf['cuid']))
		{
			$where .= sprintf(' AND cuid=%d', $searchConf['cuid']);
		}
		if (!empty($searchConf['type']))
		{
			$where .= sprintf(' AND type=%d', $searchConf['type']);
		}
		if (!empty($searchConf['start_time']))
		{
			$where .= sprintf(' AND mtime>="%s"', $searchConf['start_time']);
		}
		if (!empty($searchConf['end_time']))
		{
			$where .= sprintf(' AND mtime<="%s"', $searchConf['end_time']);
		}
		if (!empty($searchConf['line_id']))
        {
            $where .= sprintf(' AND line_id = %d', $searchConf['line_id']);
        }

		return $where;
	}
}