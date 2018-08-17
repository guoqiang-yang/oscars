<?php

/**
 * 我的拜访相关业务
 */
class Crm2_Customer_Visit extends Base_Func
{
    private $visitDao;

    public function __construct()
    {
        $this->visitDao = new Data_Dao('t_customer_visit');

        parent::__construct();
    }

    /**
     * 添加拜访
     * @param array $info
     *
     * @return mixed
     */
    public function add(array $info)
    {
        assert($info['cid'] > 0);

        return $this->visitDao->add($info);
    }

    /**
     * 修改拜访
     * @param       $id
     * @param array $info
     *
     * @return int
     */
    public function update($id, array $info)
    {
        return $this->visitDao->update($id, $info);
    }

    public function updateWhere($where, array $info)
    {
        return $this->visitDao->updateWhere($where, $info);
    }

    /**
     * 获取拜访信息
     * @param $id
     *
     * @return array
     */
    public function get($id)
    {
        return $this->visitDao->get($id);
    }

    /**
     * 获取多个拜访信息
     * @param array $ids
     *
     * @return array
     */
    public function getBulk(array $ids)
    {
        return $this->visitDao->getList($ids);
    }

    /**
     * 获取全部拜访信息
     * @param $fields
     *
     * @return array
     */
    public function getAll($fields = array('*'))
    {
        return $this->visitDao->setFields($fields)->getAll();
    }

    /**
     * 删除拜访信息
     * @param $pid
     *
     * @return bool
     */
    public function delete($id)
    {
        return $this->visitDao->delete($id);
    }

    /**
     * 根据where条件查询拜访信息
     * @param $where
     * @param $start
     * @param $num
     *
     * @return array
     */
    public function getListByWhere($where, $start = 0, $num = 20, $field=array('*'), $withPK=true)
    {
        return $this->visitDao
                    ->setFields($field)
                    ->limit($start, $num)
                    ->order('order by id desc')
                    ->getListWhere($where, $withPK);
    }

    public function getTotal($where)
    {
        return $this->visitDao->getTotal($where);
    }

    /**
     * 根据Conf数组查询拜访
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
        $total = $this->visitDao->getTotal($where);
        if ($total <= 0)
        {
            return array();
        }
        $invoiceList = $this->visitDao->order('order by id desc')->limit($start, $num)->getListWhere($where);

        return $invoiceList;
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
        if(!empty($conf['from_day']))
        {
            $where .= sprintf(' AND visit_time>="%s"', $conf['from_day']);
        }
        if(!empty($conf['end_day']))
        {
            $where .= sprintf(' AND visit_time<="%s"', $conf['end_day']);
        }

        if (!empty($conf['visit_type']))
        {
            $where .= sprintf(' AND visit_type=%d', $conf['visit_type']);
        }

        if(!empty($conf['type']))
        {
            if($conf['type'] == 1)
            {
                $where .= sprintf(' AND ctime<="%s"', date('Y-m-d H:i:s',time()-Conf_Crm::EDIT_VISIT_INTERVAL));
            }elseif($conf['type'] == 2){
                $where .= sprintf(' AND ctime>"%s"', date('Y-m-d H:i:s',time()-Conf_Crm::EDIT_VISIT_INTERVAL));
            }
        }

        if (!empty($conf['cid']))
        {
            $where .= sprintf(' AND cid=%d', $conf['cid']);
        }

        if (!empty($conf['city_id']))
        {
            $where .= sprintf(' AND city_id=%d', $conf['city_id']);
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