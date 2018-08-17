<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/9/13
 * Time: 17:36
 */
class Crm2_Certification extends Base_New_Func
{
    public function __construct()
    {
        parent::__construct('t_customer_certification');
    }

    public function getByCid($cid)
    {
        $where = sprintf('status=0 and cid=%d', $cid);
        $list = $this->limit(0, 1)->getList($where);
        if (empty($list))
        {
            return array();
        }

        return array_shift($list);
    }

    public function updateByCid($cid, $update, $change = array())
    {
        $where = sprintf('cid=%d', $cid);

        return $this->updateWhere($where, $update, $change);
    }
}