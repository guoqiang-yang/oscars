<?php
/**
 * 订单操作日志
 */
class Admin_Order_Log extends Base_Func
{
    private $orderActionLogDao;

    public function __construct()
    {
        $this->orderActionLogDao = new Data_Dao('t_order_action_log');
        parent::__construct();
    }

    /**
     * 添加
     * @param array $info
     * @return mixed
     */
	public function add(array $info)
	{
		return $this->orderActionLogDao->add($info);
	}

    public function getList($conf, $start = 0, $num = 20)
    {
        $where = $this->_getWhereByConf($conf);

        // 查询数量
        $total = $this->orderActionLogDao->getTotal($where);
        if ($total <= 0)
        {
            return array('total' => $total, 'list' => array());
        }

        // 查询结果
        $list = $this->orderActionLogDao->order('lid', 'desc')->limit($start, $num)->getListWhere($where);

        return array('total' => $total, 'list' => $list);
    }

    public function getAll($where){
        // 查询数量
        $total = $this->orderActionLogDao->getTotal($where);
        if ($total <= 0)
        {
            return array('total' => $total, 'list' => array());
        }

        // 查询结果
        $list = $this->orderActionLogDao->order('lid', 'desc')->getListWhere($where);

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
        if (!empty($searchConf['start_time']))
        {
            $where .= sprintf(' AND mtime>="%s"', $searchConf['start_time']);
        }
        if (!empty($searchConf['end_time']))
        {
            $where .= sprintf(' AND mtime<="%s"', $searchConf['end_time']);
        }

        return $where;
    }
}
