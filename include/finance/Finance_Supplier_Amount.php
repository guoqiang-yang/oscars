<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/5/25
 * Time: 上午11:17
 */
class Finance_Supplier_Amount extends Base_Func
{
    private $_dao;

    public function __construct()
    {
        $this->_dao = new Data_Dao('t_supplier_amount_history');

        parent::__construct();
    }

    /**
     * 获取供应商每日明细预付余额
     * @author libaolong
     * @param $conf
     * @return array
     */
    public function getPerDayAmountList($conf)
    {
        $prepay = 'group by type, payment_type, sid';
        $prepayFields = array('type', 'payment_type', 'suid', 'sid', 'sum(price) price', 'count(1) as sum');
        $where = $this->_genPerDayWhere($conf);

        return $this->_dao->setFields($prepayFields)->getListWhere($where.$prepay, false);
    }

    /**
     * 获取账单明细
     * @author libaolong
     * @param $conf
     * @param array $fields
     * @return array
     */
    public function getBillDetail($conf, array $fields = array('*'))
    {
        $where = $this->_genPerDayWhere($conf);
        $group = 'group by type, sid, payment_type';

        return $this->_dao->setFields($fields)->getListWhere($where . $group);
    }

    /**
     * 格式化where
     * @author libaolong
     * @param $conf
     * @return string
     */
    public function _genPerDayWhere($conf)
    {
        $where = sprintf(' 1=1 and price>0 ');

        if (!empty($conf['suid']))
        {
            $where .= sprintf(' and suid=%d ', $conf['suid']);
        }
        if (!empty($conf['start_date']))
        {
            $where .= sprintf(' and ctime>="%s" ', $conf['start_date'].' 00:00:00');
        }
        if (!empty($conf['end_date']))
        {
            $where .= sprintf(' and ctime<="%s" ', $conf['end_date'].' 23:59:59');
        }
        if (!empty($conf['paid_source']))
        {
            $where .= sprintf(' and payment_type=%d ', $conf['paid_source']);
        }
        if (empty($conf['type']) || $conf['type']==Conf_Money_Out::FINANCE_PRE_PAY)
        {
            $where .= sprintf(' and type=%d ', Conf_Finance::AMOUNT_TYPE_PREPAY);
        }

        return $where;
    }
}