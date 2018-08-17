<?php
/**
 * 订单的销售优惠记录
 */
class Order_Sale_Preferential extends Base_Func
{
    private $preferentialDao;

    public function __construct()
    {
        $this->preferentialDao = new Data_Dao('t_sale_preferential_send_record');
        parent::__construct();
    }

    public function add($info)
    {
        $info['status'] = Conf_Base::STATUS_NORMAL;
        return $this->preferentialDao->add($info);
    }

    public function update($oid, $update, $change = array())
    {
        return $this->preferentialDao->update($oid, $update, $change);
    }
    public function updateItem($where, $update, $change = array())
    {
        return $this->preferentialDao->updateWhere($where, $update, $change);
    }

    public function delete($oid)
    {
        $info = array(
            'status' => Conf_Base::STATUS_DELETED,
        );

        return $this->preferentialDao->update($oid, $info);
    }

    public function getItem($oid, $isMaster = FALSE)
    {
        if (!$isMaster)
        {
            $data = $this->preferentialDao->setSlave()->order('ctime','desc')->getListWhere('oid='.$oid.' AND status=0', false);
        }
        else
        {
            $data = $this->preferentialDao->order('ctime','desc')->getListWhere('oid='.$oid.' AND status=0', false);
        }
        return $data;
    }

    public function getListRawWhere($searchConf, &$total, $order, $start = 0, $num = 20, $fields = array('*'), $withPk = true)
    {
        $where = sprintf(' 1=1 and status=%d ', Conf_Base::STATUS_NORMAL);
        if (!empty($searchConf['send_suid']))
        {
            $where .= sprintf(' and send_suid=%d ', $searchConf['send_suid']);
        }
        if (!empty($searchConf['oid']))
        {
            $where .= sprintf(' and oid=%d ', $searchConf['oid']);
        }
        if (!empty($searchConf['bdate']))
        {
            $where .= sprintf(" and ctime>='%s' ", date('Y-m-d 00:00:00', strtotime($searchConf['bdate'])));
        }
        if (!empty($searchConf['edate']))
        {
            $where .= sprintf(" and ctime<='%s' ", date('Y-m-d 23:59:59', strtotime($searchConf['edate'])));
        }
        if(!empty($searchConf['month']))
        {
            $where .= sprintf(' and month="%s" ', $searchConf['month']);
        }

        $total = $this->preferentialDao->setSlave()->getTotal($where);
        if ($total <= 0)
        {
            return array();
        }

        if (empty($order))
        {
            $order = array(
                'ctime',
                'desc'
            );
        }

        $list = $this->preferentialDao->setSlave()->order($order[0], $order[1])->limit($start, $num)->setFields($fields)->getListWhere($where, $withPk);
        if(!empty($list))
        {
            $suids = Tool_Array::getFields($list, 'send_suid');
            $saleInfos = Admin_Api::getStaffs($suids);
            $saleInfos = Tool_Array::list2Map($saleInfos, 'suid');
            $totalPrivilege = 0;
            foreach ($list as &$item)
            {
                $totalPrivilege += $item['amount']/100;
                $item['send_name'] = $saleInfos[$item['send_suid']]['name'];
            }
        }
        return array($list, $totalPrivilege);
    }

    public function sumMonthlyOrderPreferentialAmountBySender($suid, $begin_time, $end_time)
    {
        $where = sprintf('status=%d AND send_suid=%d AND ctime>="%s" AND ctime<"%s"', Conf_Base::STATUS_NORMAL, $suid, $begin_time, $end_time);
        return $this->preferentialDao->setSlave()->getSum('amount', $where);
    }
}
