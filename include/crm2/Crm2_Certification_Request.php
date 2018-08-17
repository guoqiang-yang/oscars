<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/9/13
 * Time: 17:36
 */
class Crm2_Certification_Request extends Base_New_Func
{
    public function __construct()
    {
        parent::__construct('t_certificate_list');
    }

    public function getByCid($cid)
    {
        $where = sprintf('cid=%d', $cid);
        $list = $this->limit(0, 0)->getList($where);

        return $list;
    }

    public function getUndealItem($suid, $cid)
    {
        $where = sprintf('cid=%d AND result=%d', $cid, Conf_User::CERTIFICATE_RESULT_UNDEAL);
        $list = $this->limit(0, 1)->getList($where);
        if (empty($list))
        {
            return array();
        }

        return array_shift($list);
    }

    public function pass($suid, $cid)
    {
        $update = array(
            'suid' => $suid,
            'result' => Conf_User::CERTIFICATE_RESULT_PASS,
            'deal_time' => date('Y-m-d H:i:s'),
        );

        $where = sprintf('cid=%d AND result=%d', $cid, Conf_User::CERTIFICATE_RESULT_UNDEAL);

        return $this->updateWhere($where, $update);
    }
    
    public function deny($suid, $cid, $reason = '')
    {
        $update = array(
            'suid' => $suid,
            'result' => Conf_User::CERTIFICATE_RESULT_DENY,
            'deal_time' => date('Y-m-d H:i:s'),
            'reason' => $reason,
        );

        $where = sprintf('cid=%d AND result=%d', $cid, Conf_User::CERTIFICATE_RESULT_UNDEAL);

        return $this->updateWhere($where, $update);
    }

    public function updateBySuidCid($suid, $cid, $update)
    {
        $where = sprintf('suid=%d AND cid=%d', $suid, $cid);

        return $this->updateWhere($where, $update);
    }
}