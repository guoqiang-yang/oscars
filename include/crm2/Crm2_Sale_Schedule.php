<?php

/**
 * 我的日程相关业务
 */
class Crm2_Sale_Schedule extends Base_Func
{
    private $scheduleDao;

    public function __construct()
    {
        $this->scheduleDao = new Data_Dao('t_sale_schedule');

        parent::__construct();
    }

    /**
     * 添加日程
     * @param array $info
     *
     * @return mixed
     */
    public function add(array $info)
    {
        assert($info['suid'] > 0);

        return $this->scheduleDao->add($info);
    }

    /**
     * 修改日程
     * @param       $id
     * @param array $info
     *
     * @return int
     */
    public function update($id, array $info)
    {
        return $this->scheduleDao->update($id, $info);
    }

    public function updateWhere($where, array $info)
    {
        return $this->scheduleDao->updateWhere($where, $info);
    }

    /**
     * 获取日程信息
     * @param $id
     *
     * @return array
     */
    public function get($id)
    {
        return $this->scheduleDao->get($id);
    }

    /**
     * 获取多个日程信息
     * @param array $ids
     *
     * @return array
     */
    public function getBulk(array $ids)
    {
        return $this->scheduleDao->getList($ids);
    }

    /**
     * 获取全部日程信息
     * @param $fields
     *
     * @return array
     */
    public function getAll($fields = array('*'))
    {
        return $this->scheduleDao->setFields($fields)->getAll();
    }

    /**
     * 删除日程信息
     * @param $pid
     *
     * @return bool
     */
    public function delete($id)
    {
        return $this->scheduleDao->delete($id);
    }

    /**
     * 根据where条件查询日程信息
     * @param $where
     * @param $start
     * @param $num
     *
     * @return array
     */
    public function getListByWhere($where, $start = 0, $num = 20, $fields=array('*'), $withPK=true)
    {
        return $this->scheduleDao->setFields($fields)->limit($start, $num)->order('order by schedule_time asc')->getListWhere($where, $withPK);
    }

    /**
     * 根据Conf数组查询日程
     * @param     $conf
     * @param int $total
     * @param int $start
     * @param int $num
     * @param int $stausTag
     *
     * @return array
     */
    public function getList($conf, &$total, $start = 0, $num = 20)
    {
        $where = $this->_getWhereByConf($conf);
        $total = $this->scheduleDao->getTotal($where);
        if ($total <= 0)
        {
            return array();
        }
        $scheduleList = $this->scheduleDao->order('order by schedule_time desc')->limit($start, $num)->getListWhere($where);

        return $scheduleList;
    }

    /**
     * 查询符合条件的记录数
     * @param $where
     *
     * @return bool|int
     */
    public function getTotal($where)
    {
        return $this->scheduleDao->getTotal($where);
    }
    /**
     * 根据conf生成where语句
     * @param     $conf
     * @param int $stausTag
     *
     * @return string
     */
    private function _getWhereByConf($conf)
    {
        $where = 'status='.Conf_Base::STATUS_NORMAL;

        if (!empty($conf['id']))
        {
            if (is_array($conf['id']))
            {
                $where .= " AND id in ('" . implode("','", $conf['id']) . "') ";
            }
            else
            {
                $where .= sprintf(' AND id=%d', $conf['id']);
            }
        }
        if(!empty($conf['type']))
        {
            if($conf['type'] == 1){
                $where .= sprintf(' AND schedule_time>"%s"',date('Y-m-d H:i:s', time()));
            }
            elseif($conf['type'] == 2)
            {
                $where .= sprintf(' AND schedule_time<="%s"',date('Y-m-d H:i:s', time()));
            }
        }
        if(!empty($conf['from_day']))
        {
            $where .= sprintf(' AND schedule_time>="%s"', $conf['from_day']);
        }
        if(!empty($conf['end_day']))
        {
            $where .= sprintf(' AND schedule_time<="%s"', $conf['end_day']);
        }
        if(!empty($conf['from_ctime']))
        {
            $where .= sprintf(' AND remind_time>="%s 00:00:00"', $conf['from_ctime']);
        }
        if(!empty($conf['end_ctime']))
        {
            $where .= sprintf(' AND remind_time<="%s 23:59:59"', $conf['end_ctime']);
        }
        if (!empty($conf['has_remind']))
        {
            $where .= sprintf(' AND has_remind=%d', $conf['has_remind']);
        }

        if (!empty($conf['cid']))
        {
            $where .= sprintf(' AND cid=%d', $conf['cid']);
        }

        if (!empty($conf['suid']))
        {
            if(is_array($conf['suid']))
            {
                $where .= sprintf(' AND suid IN(%s)', implode(',', $conf['suid']));
            }else{
                $where .= sprintf(' AND suid=%d', $conf['suid']);
            }
        }
        return $where;
    }
}